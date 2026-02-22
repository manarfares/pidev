# Guide: Where to Find Covoiturage Participant Requests

**Date:** February 22, 2026
**Status:** ✅ WORKING

---

## 🎯 Summary

When you make a covoiturage booking request, it creates a `Participant` record with status `en_attente` (pending). Here's where to find and manage these requests:

---

## 📍 Where to Find Participant Requests

### 1. **As a Driver (Trip Creator)** - `/covoiturage/mes-trajets`

**URL:** http://localhost:8000/covoiturage/mes-trajets

**What you'll see:**
- All trips you created as a driver
- For each trip with pending requests, you'll see an **orange alert box** showing:
  - Number of pending requests
  - Participant details (name, email, request date)
  - **Accept** button (green) - Changes status to `confirme`
  - **Reject** button (red) - Changes status to `refuse`

**Features:**
- Real-time display of pending requests
- One-click accept/reject
- View confirmed participants
- See available places

---

### 2. **As Admin** - `/admin/covoiturages`

**URL:** http://localhost:8000/admin/covoiturages

**Steps to view participants:**
1. Go to Admin Panel → Covoiturages
2. Find the covoiturage in the list
3. Click "Voir participants" or the participants icon
4. You'll be redirected to `/admin/covoiturages/{id}/participants`

**Admin Participants Page Features:**
- Statistics cards (pending, confirmed, refused, cancelled)
- Full list of all participants
- Change participant status dropdown
- Delete participant option
- View participant details (email, request date, message)

---

### 3. **As a Passenger** - `/covoiturage/mes-trajets`

**URL:** http://localhost:8000/covoiturage/mes-trajets

**What you'll see:**
- Section "Mes participations" showing all your booking requests
- Status badges:
  - 🟠 **En attente** - Waiting for driver approval
  - 🟢 **Confirmé** - Driver accepted your request
  - 🔴 **Refusé** - Driver rejected your request
  - ⚫ **Annulé** - You cancelled your request
- Trip details (departure, destination, date, driver info)
- Cancel button (if confirmed)

---

## 🔍 Current Participant Requests in Database

Based on the database query, you have:

| ID | Passenger ID | Covoiturage ID | Status | Date Created |
|----|--------------|----------------|---------|--------------|
| 3 | 2 | 1 | en_attente | 2026-02-22 15:41:28 |
| 2 | 2 | 3 | en_attente | 2026-02-22 15:35:45 |

**This means:**
- User #2 (user@test.com) made 2 booking requests
- Request for Covoiturage #1 (created by user #3 - host@test.com)
- Request for Covoiturage #3 (created by user #3 - host@test.com)
- Both are pending approval

---

## 🚗 How to Test

### As the Driver (host@test.com):

1. **Login:**
   - Go to http://localhost:8000/login
   - Email: `host@test.com`
   - Password: (your password)

2. **View Requests:**
   - Go to http://localhost:8000/covoiturage/mes-trajets
   - You should see your trips with **orange alert boxes**
   - Each box shows pending participant requests

3. **Accept/Reject:**
   - Click "✓ Accepter" to confirm the participant
   - Click "✕ Refuser" to reject the participant

### As the Passenger (user@test.com):

1. **Login:**
   - Go to http://localhost:8000/login
   - Email: `user@test.com`
   - Password: (your password)

2. **View Your Requests:**
   - Go to http://localhost:8000/covoiturage/mes-trajets
   - Scroll to "Mes participations" section
   - You'll see your 2 pending requests with status badges

### As Admin (admin@test.com):

1. **Login:**
   - Go to http://localhost:8000/login
   - Email: `admin@test.com`
   - Password: (your password)

2. **View All Participants:**
   - Go to http://localhost:8000/admin/covoiturages
   - Find a covoiturage with participants
   - Click "Voir participants"
   - Manage participant statuses

---

## 📊 Participant Status Flow

```
User books a trip
       ↓
Status: en_attente (pending)
       ↓
Driver reviews request
       ↓
    ┌─────┴─────┐
    ↓           ↓
Accept      Reject
    ↓           ↓
confirme    refuse
```

**Status Values:**
- `en_attente` - Pending driver approval
- `confirme` - Driver accepted
- `refuse` - Driver rejected
- `annule` - Passenger cancelled

---

## 🎨 Visual Indicators

### Driver View (My Trips):
- **Orange box** = Pending requests need attention
- **Green box** = Confirmed participants
- **Badges** show counts

### Passenger View (My Participations):
- **Orange badge** (⏳) = Waiting for approval
- **Green badge** (✓) = Confirmed
- **Red badge** (✕) = Refused
- **Gray badge** (✕) = Cancelled

### Admin View:
- **Statistics cards** with color coding
- **Status badges** in table
- **Dropdown menu** to change status

---

## 🔧 Technical Details

### Routes:
```php
// Driver actions
POST /covoiturage/participant/{id}/accept  - Accept participant
POST /covoiturage/participant/{id}/reject  - Reject participant

// Admin actions
GET  /admin/covoiturages/{id}/participants - View participants
POST /admin/participant/{id}/status        - Change status
POST /admin/participant/{id}/delete        - Delete participant

// Passenger actions
POST /covoiturage/{id}/reserver            - Book a trip
POST /covoiturage/{id}/annuler             - Cancel booking
```

### Database Table:
```sql
CREATE TABLE `participant` (
  `id` int(11) NOT NULL,
  `passager_id` int(11) NOT NULL,
  `covoiturage_id` int(11) NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'en_attente',
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `message` text DEFAULT NULL
);
```

---

## ✅ Verification Steps

1. **Check if requests exist:**
   ```bash
   php bin/console doctrine:query:sql "SELECT * FROM participant WHERE statut='en_attente'"
   ```

2. **Check covoiturage details:**
   ```bash
   php bin/console doctrine:query:sql "SELECT * FROM covoiturage WHERE id IN (1,3)"
   ```

3. **Check user details:**
   ```bash
   php bin/console doctrine:query:sql "SELECT id, email FROM user WHERE id IN (2,3)"
   ```

---

## 🐛 Troubleshooting

### "I don't see my requests"

**Check:**
1. Are you logged in as the correct user?
2. Go to the correct page:
   - Driver: `/covoiturage/mes-trajets`
   - Passenger: `/covoiturage/mes-trajets` (scroll to "Mes participations")
   - Admin: `/admin/covoiturages` → Click "Voir participants"

### "The orange box doesn't appear"

**Possible causes:**
1. No pending requests (all are confirmed/refused)
2. Cache issue - Clear cache: `php bin/console cache:clear`
3. Check database: Run SQL query above

### "Accept/Reject buttons don't work"

**Check:**
1. CSRF token is valid
2. You're the trip owner
3. Check browser console for errors
4. Verify routes are registered: `php bin/console debug:router | grep participant`

---

## 📝 Summary

**Your participant requests ARE in the system!**

They're visible in 3 places:
1. ✅ Driver's "My Trips" page (orange alert boxes)
2. ✅ Passenger's "My Participations" section
3. ✅ Admin's covoiturage participants page

The logic is working correctly. Just navigate to the right page based on your role!

---

**Generated by Kiro AI Assistant**
**Date: February 22, 2026**
