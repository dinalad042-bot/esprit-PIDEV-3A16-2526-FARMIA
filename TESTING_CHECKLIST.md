# ✅ FARM SELECTOR FIX — TESTING CHECKLIST

**Date:** 2026-05-06  
**Status:** Ready for Manual Testing  
**Dev Server:** Running on localhost:8000

---

## 🧪 MANUAL TESTING CHECKLIST

### Phase 1: UI Verification

- [ ] **Navigate to form**
  - URL: `http://localhost:8000/agricole/nouvelle-demande`
  - Expected: Form loads without errors

- [ ] **Verify farm dropdown exists**
  - Expected: Dropdown appears (not static text)
  - Expected: Label says "Sélectionner une ferme *"
  - Expected: Dropdown is required (marked with *)

- [ ] **Verify all farms are listed**
  - Expected: All user's farms appear in dropdown
  - Expected: Each farm shows its name (e.g., "olo", "zzz")
  - Expected: First farm is pre-selected

- [ ] **Verify dropdown styling**
  - Expected: Dropdown matches other form fields
  - Expected: Dropdown has proper padding and border
  - Expected: Dropdown is responsive

### Phase 2: Functionality Testing

- [ ] **Test farm selection**
  - Action: Click dropdown
  - Expected: All farms are visible
  - Action: Select different farm
  - Expected: Selection changes

- [ ] **Test animals/plants update**
  - Action: Select farm "olo"
  - Expected: Animals dropdown shows animals from "olo"
  - Action: Select farm "zzz"
  - Expected: Animals dropdown updates to show animals from "zzz"

- [ ] **Test form submission with farm 1**
  - Action: Select farm "olo"
  - Action: Fill in description
  - Action: Click "Soumettre la demande"
  - Expected: Form submits successfully
  - Expected: Redirects to "Mes Demandes" page
  - Expected: Success message appears

- [ ] **Test form submission with farm 2**
  - Action: Go back to form
  - Action: Select farm "zzz"
  - Action: Fill in description
  - Action: Click "Soumettre la demande"
  - Expected: Form submits successfully
  - Expected: Redirects to "Mes Demandes" page
  - Expected: Success message appears

### Phase 3: Data Verification

- [ ] **Verify analysis created for correct farm**
  - Action: Go to "Mes Demandes" page
  - Expected: Both analysis requests are listed
  - Expected: First request shows farm "olo"
  - Expected: Second request shows farm "zzz"

- [ ] **Verify Expert can see correct farm**
  - Action: Login as Expert
  - Action: Go to `/expert/analyses`
  - Expected: Both analyses are listed
  - Expected: Each analysis shows correct farm
  - Expected: Can filter by farm if available

- [ ] **Verify animals/plants are correct**
  - Action: Click on analysis for farm "olo"
  - Expected: Shows animals/plants from farm "olo"
  - Action: Click on analysis for farm "zzz"
  - Expected: Shows animals/plants from farm "zzz"

### Phase 4: Edge Cases

- [ ] **Test with no farm selected**
  - Action: Try to submit form without selecting farm
  - Expected: Form validation error appears
  - Expected: Message indicates farm is required

- [ ] **Test with invalid farm ID**
  - Action: Manually edit URL to pass invalid farm ID
  - Expected: Form defaults to first farm
  - Expected: No errors occur

- [ ] **Test with single farm user**
  - Action: Login as user with only one farm
  - Expected: Dropdown shows only one farm
  - Expected: That farm is pre-selected
  - Expected: Form works normally

- [ ] **Test with no farms user**
  - Action: Login as user with no farms
  - Expected: Redirects to farm creation page
  - Expected: Message says "Vous devez d'abord créer une ferme..."

### Phase 5: Browser Compatibility

- [ ] **Test in Chrome**
  - Expected: Dropdown works correctly
  - Expected: No console errors

- [ ] **Test in Firefox**
  - Expected: Dropdown works correctly
  - Expected: No console errors

- [ ] **Test in Edge**
  - Expected: Dropdown works correctly
  - Expected: No console errors

### Phase 6: Performance

- [ ] **Test form load time**
  - Expected: Form loads quickly (< 2 seconds)
  - Expected: No lag when opening dropdown

- [ ] **Test with many farms**
  - Action: Create 10+ farms
  - Expected: Dropdown still works smoothly
  - Expected: No performance issues

---

## 📋 REGRESSION TESTING

- [ ] **Verify other form fields still work**
  - [ ] Animal selector works
  - [ ] Plant selector works
  - [ ] Description textarea works
  - [ ] Image upload works

- [ ] **Verify other pages still work**
  - [ ] Dashboard loads
  - [ ] "Mes Fermes" page works
  - [ ] "Mes Demandes" page works
  - [ ] Expert panel works

- [ ] **Verify no errors in logs**
  - [ ] No PHP errors
  - [ ] No JavaScript errors
  - [ ] No database errors

---

## ✅ SIGN-OFF CHECKLIST

### Code Quality
- [ ] PHP syntax is valid
- [ ] Twig syntax is valid
- [ ] No undefined variables
- [ ] No undefined methods
- [ ] Security validation in place

### Functionality
- [ ] Farm dropdown appears
- [ ] All farms are selectable
- [ ] Selected farm is saved
- [ ] Animals/plants update dynamically
- [ ] Form submits successfully

### Data Integrity
- [ ] Analysis created for correct farm
- [ ] Expert can see correct farm
- [ ] No data mixing between farms
- [ ] No orphaned records

### User Experience
- [ ] Dropdown is intuitive
- [ ] Styling matches other fields
- [ ] Error messages are clear
- [ ] Success messages appear
- [ ] No unexpected redirects

### Performance
- [ ] Form loads quickly
- [ ] Dropdown responds immediately
- [ ] No lag or freezing
- [ ] Works with many farms

---

## 🐛 BUG REPORT TEMPLATE

If you find any issues, please report them with:

```
Title: [Brief description]
Severity: [Critical/High/Medium/Low]
Steps to Reproduce:
1. [Step 1]
2. [Step 2]
3. [Step 3]

Expected Result:
[What should happen]

Actual Result:
[What actually happened]

Screenshots:
[If applicable]

Browser/Environment:
[Browser, OS, etc.]
```

---

## 📞 SUPPORT

If you encounter any issues:

1. Check the browser console for errors (F12)
2. Check the server logs
3. Review the documentation files
4. Report the issue with the template above

---

## ✅ FINAL SIGN-OFF

When all tests pass, sign off here:

- [ ] All manual tests passed
- [ ] No regressions found
- [ ] No bugs discovered
- [ ] Ready for production

**Tested by:** ___________________  
**Date:** ___________________  
**Status:** ✅ APPROVED / ❌ NEEDS FIXES

---

**Status:** Ready for Testing

Dev server is running on `localhost:8000`. Begin testing now!

