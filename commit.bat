@echo off
cd "C:\Users\sliti\Documents\web\pre-release\teammate-farmai"
git add -A
git commit -m "%~1"
git push origin Alaeddin-expertise-branch
echo Done! Committed: %~1