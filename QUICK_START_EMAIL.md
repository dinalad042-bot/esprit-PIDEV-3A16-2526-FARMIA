# 📧 QUICK START — EMAIL VERIFICATION

**Status:** ✅ CONFIGURED AND READY

---

## 🚀 TEST EMAIL VERIFICATION NOW

### Step 1: Start Dev Server
```bash
php -S localhost:8000 -t public public/router.php
```

### Step 2: Go to Signup Page
```
http://localhost:8000/signup
```

### Step 3: Fill in the Form
- **Name:** Test User
- **Email:** your-email@example.com
- **Password:** TestPassword123!
- **Role:** Select a role
- **Other fields:** Fill as needed

### Step 4: Submit
- Click "Sign Up"
- You should see: "Un code de vérification a été envoyé à votre adresse email."

### Step 5: Check Email
- Check your email inbox
- Look for email from "FarmIA Security"
- Copy the 6-digit verification code

### Step 6: Verify Code
- You'll be redirected to verification page
- Enter the 6-digit code
- Click "Verify"

### Step 7: Success!
- You should see: "Adresse email vérifiée. Inscription réussie !"
- You can now login with your credentials

---

## ⚙️ CONFIGURATION SUMMARY

| Setting | Value |
|---------|-------|
| **Email Provider** | Gmail |
| **Sender Email** | aymen.bensalem2002@gmail.com |
| **SMTP Protocol** | TLS |
| **Verification Method** | 6-digit code |
| **Code Expiration** | 15 minutes |
| **Status** | ✅ ACTIVE |

---

## 📝 WHAT WAS CHANGED

### `.env` File
```env
# BEFORE:
MAILER_DSN=null://null

# AFTER:
MAILER_DSN=gmail://aymen.bensalem2002@gmail.com:jjnwqquxsqcpxbog@default
```

### GROQ API Key
```env
# BEFORE:
GROQ_API_KEY=""

# AFTER:
GROQ_API_KEY="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"
```

---

## 🔍 HOW IT WORKS

1. **User signs up** → Fills registration form
2. **Code generated** → Random 6-digit code created
3. **Email sent** → Code sent to user's email via Gmail
4. **User verifies** → User enters code on verification page
5. **Account created** → If code is valid, account is created
6. **User logs in** → User can now login with credentials

---

## ✅ VERIFICATION CHECKLIST

- [x] `.env` configured with Gmail SMTP
- [x] GROQ API key added
- [x] Email template exists
- [x] Signup controller ready
- [x] Verification flow implemented
- [x] Dev server running
- [x] Ready for testing

---

## 🐛 TROUBLESHOOTING

### Email Not Arriving?
1. Check spam folder
2. Verify email address is correct
3. Check `.env` file has correct Gmail credentials
4. Check logs: `tail -f var/log/dev.log`

### Code Expired?
- Code expires after 15 minutes
- User must start signup process again

### SMTP Error?
- Check internet connection
- Verify Gmail app password is correct
- Check firewall allows port 587

---

## 📞 SUPPORT

For detailed information, see: `EMAIL_CONFIGURATION_FIXED.md`

---

**Status:** ✅ READY TO TEST

Start with Step 1 above!

