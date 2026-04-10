from flask import Flask, request, jsonify
import cv2
import numpy as np
import base64
import json
import time
from pathlib import Path

app = Flask(__name__)

# Dossiers
BASE_DIR = Path(__file__).resolve().parent
DATASET_DIR = BASE_DIR / "dataset"
MODEL_DIR = BASE_DIR / "model"
MODEL_PATH = MODEL_DIR / "lbph_model.yml"
LABELS_PATH = MODEL_DIR / "labels.json"

DATASET_DIR.mkdir(exist_ok=True)
MODEL_DIR.mkdir(exist_ok=True)

# Haar Cascade
CASCADE_PATH = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
face_cascade = cv2.CascadeClassifier(CASCADE_PATH)

# LBPH Face Recognizer (opencv-contrib-python requis)
if not hasattr(cv2, "face"):
    raise RuntimeError(
        "cv2.face n'est pas disponible. Installe opencv-contrib-python."
    )

recognizer = cv2.face.LBPHFaceRecognizer_create()
CONFIDENCE_THRESHOLD = 70.0  # à ajuster si besoin


def decode_base64_image(data_uri: str):
    """Convertit une image base64 en image OpenCV."""
    if "," in data_uri:
        data_uri = data_uri.split(",")[1]

    img_bytes = base64.b64decode(data_uri)
    np_arr = np.frombuffer(img_bytes, np.uint8)
    img = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)
    return img


def detect_largest_face(gray_img):
    """Détecte le plus grand visage dans l'image."""
    faces = face_cascade.detectMultiScale(
        gray_img,
        scaleFactor=1.1,
        minNeighbors=5,
        minSize=(100, 100)
    )

    if len(faces) == 0:
        return None

    # On prend le plus grand visage
    return max(faces, key=lambda f: f[2] * f[3])


def save_labels(label_to_user):
    """Sauvegarde la correspondance label -> user_id."""
    with open(LABELS_PATH, "w", encoding="utf-8") as f:
        json.dump({"label_to_user": label_to_user}, f, ensure_ascii=False, indent=2)


def load_labels():
    """Charge la correspondance label -> user_id."""
    if not LABELS_PATH.exists():
        return {}
    with open(LABELS_PATH, "r", encoding="utf-8") as f:
        data = json.load(f)
    return data.get("label_to_user", {})


def train_model():
    """
    Entraîne le modèle LBPH à partir des visages enregistrés dans dataset/<user_id>/.
    Chaque image du dataset doit être un visage déjà découpé (grayscale).
    """
    images = []
    labels = []
    user_to_label = {}
    label_to_user = {}
    current_label = 0

    if not DATASET_DIR.exists():
        return {"success": False, "message": "Aucune donnée d'entraînement trouvée."}

    for user_dir in DATASET_DIR.iterdir():
        if not user_dir.is_dir():
            continue

        user_id = user_dir.name

        if user_id not in user_to_label:
            user_to_label[user_id] = current_label
            label_to_user[str(current_label)] = user_id
            current_label += 1

        for img_path in user_dir.glob("*.png"):
            face_img = cv2.imread(str(img_path), cv2.IMREAD_GRAYSCALE)
            if face_img is None:
                continue

            face_img = cv2.resize(face_img, (200, 200))
            images.append(face_img)
            labels.append(user_to_label[user_id])

    if len(images) == 0:
        return {"success": False, "message": "Aucun visage valide trouvé pour l'entraînement."}

    recognizer.train(images, np.array(labels))
    recognizer.write(str(MODEL_PATH))
    save_labels(label_to_user)

    return {
        "success": True,
        "message": "Modèle entraîné avec succès.",
        "users_count": len(user_to_label),
        "samples_count": len(images)
    }


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"success": True, "message": "API Python OK"})


@app.route("/api/enroll", methods=["POST"])
def enroll_face():
    """
    Enregistre le visage d'un utilisateur.
    Attendu en JSON:
    {
        "user_id": "123",
        "image": "data:image/png;base64,..."
    }
    """
    data = request.get_json(silent=True) or {}
    user_id = str(data.get("user_id", "")).strip()
    image_data = data.get("image")

    if not user_id or not image_data:
        return jsonify({
            "success": False,
            "message": "user_id et image sont obligatoires."
        }), 400

    try:
        img = decode_base64_image(image_data)
        if img is None:
            return jsonify({"success": False, "message": "Image invalide."}), 400

        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        face = detect_largest_face(gray)

        if face is None:
            return jsonify({"success": False, "message": "Aucun visage détecté."}), 404

        x, y, w, h = face
        face_roi = gray[y:y+h, x:x+w]
        face_roi = cv2.resize(face_roi, (200, 200))

        user_dir = DATASET_DIR / user_id
        user_dir.mkdir(parents=True, exist_ok=True)

        filename = f"{int(time.time() * 1000)}.png"
        cv2.imwrite(str(user_dir / filename), face_roi)

        # Réentraîner le modèle après ajout
        train_result = train_model()
        if not train_result["success"]:
            return jsonify({
                "success": False,
                "message": train_result["message"]
            }), 500

        return jsonify({
            "success": True,
            "message": "Visage enregistré avec succès.",
            "user_id": user_id,
            "samples_count": train_result["samples_count"]
        })

    except Exception as e:
        return jsonify({"success": False, "message": str(e)}), 500


@app.route("/api/recognize", methods=["POST"])
def recognize_face():
    """
    Reconnaît un visage.
    Attendu en JSON:
    {
        "image": "data:image/png;base64,..."
    }
    """
    data = request.get_json(silent=True) or {}
    image_data = data.get("image")

    if not image_data:
        return jsonify({
            "success": False,
            "message": "image est obligatoire."
        }), 400

    if not MODEL_PATH.exists() or not LABELS_PATH.exists():
        return jsonify({
            "success": False,
            "message": "Aucun modèle entraîné n'existe encore."
        }), 404

    try:
        # Charger labels et modèle
        label_to_user = load_labels()
        recognizer.read(str(MODEL_PATH))

        img = decode_base64_image(image_data)
        if img is None:
            return jsonify({"success": False, "message": "Image invalide."}), 400

        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        face = detect_largest_face(gray)

        if face is None:
            return jsonify({"success": False, "message": "Aucun visage détecté."}), 404

        x, y, w, h = face
        face_roi = gray[y:y+h, x:x+w]
        face_roi = cv2.resize(face_roi, (200, 200))

        label, confidence = recognizer.predict(face_roi)

        # LBPH: plus la confidence est faible, mieux c'est
        if confidence <= CONFIDENCE_THRESHOLD and str(label) in label_to_user:
            user_id = label_to_user[str(label)]
            return jsonify({
                "success": True,
                "user_id": user_id,
                "confidence": float(confidence),
                "message": "Visage reconnu avec succès."
            })

        return jsonify({
            "success": False,
            "message": "Visage non reconnu.",
            "confidence": float(confidence)
        }), 401

    except Exception as e:
        return jsonify({"success": False, "message": str(e)}), 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)