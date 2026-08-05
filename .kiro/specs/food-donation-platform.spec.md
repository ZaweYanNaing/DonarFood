# DonarFood - Food Donation Social Platform

## Project Overview

A social food donation platform where volunteers create donation issues and donators pledge food. Features include real-time chat, image uploads, friend system, and profile sharing.

**Tech Stack:** Laravel 13.x + React 19 + TypeScript + MySQL + Docker + WebSockets (Laravel Reverb)

---

## Phase 1: Infrastructure Setup

### Task 1.1: Docker Configuration
**Status:** completed  
**Priority:** highest

Created `docker-compose.yml` with:
- Laravel Sail (PHP 8.3)
- MySQL 8.4
- PHPMyAdmin (port 8081)
- Redis
- Mailpit (mail testing)

**Files:**
- `docker-compose.yml`

### Task 1.2: Environment Configuration
**Status:** completed  
**Priority:** highest

Updated `.env`:
- DB_CONNECTION=mysql (from sqlite)
- Configured MySQL host/credentials for Docker
- Redis for caching
- Reverb for WebSockets
- Mailpit for mail testing

**Files:**
- `.env`

### Task 1.3: Database Schema Migrations
**Status:** completed  
**Priority:** highest

Created migrations:
1. `donation_issues` - volunteer-created donation requests
2. `donations` - food pledges by donators
3. `images` - polymorphic for all image types
4. `friendships` - user friendship relationships
5. `issue_messages` - chat messages under donation issues
6. `private_messages` - direct messages between friends
7. `user_profiles` - extended user profile data (with profile_photo_path on users)

**Files:**
- `database/migrations/2026_08_05_035206_create_donation_issues_table.php`
- `database/migrations/2026_08_05_035228_create_donations_table.php`
- `database/migrations/2026_08_05_035229_create_images_table.php`
- `database/migrations/2026_08_05_035230_create_friendships_table.php`
- `database/migrations/2026_08_05_035231_create_issue_messages_table.php`
- `database/migrations/2026_08_05_035232_create_private_messages_table.php`
- `database/migrations/2026_08_05_035233_create_user_profiles_table.php`

### Task 1.4: Models
**Status:** completed  
**Priority:** high

Created models with relationships:
- `DonationIssue` - belongsTo User, hasMany Donations, IssueMessages
- `Donation` - belongsTo User, DonationIssue, hasMany Images (polymorphic)
- `Friendship` - belongsTo User (requester, recipient)
- `IssueMessage` - belongsTo User, DonationIssue
- `PrivateMessage` - belongsTo User (sender, recipient)
- `Image` - polymorphic for all image types
- `UserProfile` - belongsTo User
- `User` - extended with all relationships

**Files:**
- `app/Models/DonationIssue.php`
- `app/Models/Donation.php`
- `app/Models/Friendship.php`
- `app/Models/IssueMessage.php`
- `app/Models/PrivateMessage.php`
- `app/Models/Image.php`
- `app/Models/UserProfile.php`
- `app/Models/User.php` (updated)

---

## Phase 2: Core Features - Donation Issues

### Task 2.1: Donation Issue API Resources
**Status:** pending  
**Priority:** high

Create API Resources:
- `DonationIssueResource` - full issue data
- `DonationIssueCardResource` - minimal data for lists
- `DonationResource` - donation pledge data

**Files:**
- `app/Http/Resources/DonationIssueResource.php`
- `app/Http/Resources/DonationIssueCardResource.php`
- `app/Http/Resources/DonationResource.php`

### Task 2.2: Issue Controller & Routes
**Status:** pending  
**Priority:** high

Routes:
- `GET /issues` - list all (public, paginated)
- `GET /issues/{issue}` - show with donations
- `POST /issues` - create (volunteer only)
- `PUT /issues/{issue}` - update (owner only)
- `DELETE /issues/{issue}` - delete (owner only)

**Files:**
- `app/Http/Controllers/DonationIssueController.php`
- `routes/web.php` - additional routes

### Task 2.3: Donation Controller & Routes
**Status:** pending  
**Priority:** high

Routes:
- `GET /issues/{issue}/donations` - list pledges
- `POST /issues/{issue}/donations` - create pledge (donator)
- `PUT /donations/{donation}` - update pledge amount
- `DELETE /donations/{donation}` - cancel pledge

**Files:**
- `app/Http/Controllers/DonationController.php`

### Task 2.4: Issue Policy
**Status:** pending  
**Priority:** high

Authorization policies for donation issues and donations.

**Files:**
- `app/Policies/DonationIssuePolicy.php`
- `app/Policies/DonationPolicy.php`

---

## Phase 3: Image Upload System

### Task 3.1: Image Upload Controller
**Status:** pending  
**Priority:** high

Routes:
- `POST /upload/issue/{issue}` - upload to issue
- `POST /upload/donation/{donation}` - upload to donation
- `POST /upload/profile` - upload profile photo

**Files:**
- `app/Http/Controllers/ImageController.php`

### Task 3.2: File Validation & Storage
**Status:** pending  
**Priority:** medium

Implement:
- File type validation (jpg, png, webp)
- Size limit (5MB)
- Storage in `storage/app/public/uploads`

**Files:**
- `app/Rules/ValidImage.php`

---

## Phase 4: Social Features

### Task 4.1: Friendship Controller
**Status:** pending  
**Priority:** high

Routes:
- `GET /friends` - list friends
- `POST /friends/request/{user}` - send friend request
- `PUT /friends/request/{user}` - accept/reject request
- `DELETE /friends/{user}` - unfriend

**Files:**
- `app/Http/Controllers/FriendshipController.php`

### Task 4.2: User Profile Controller
**Status:** pending  
**Priority:** high

Routes:
- `GET /profile/{user}` - public profile
- `GET /profile/me` - own profile
- `PUT /profile` - update profile
- `GET /profile/{user}/donations` - user's donation activity

**Files:**
- `app/Http/Controllers/ProfileController.php`

### Task 4.3: Share Feature
**Status:** pending  
**Priority:** medium

Implement share functionality:
- Generate shareable links for donations
- Copy to clipboard UI

---

## Phase 5: Real-time Communication (WebSockets)

### Task 5.1: Issue Chat (Reverb)
**Status:** pending  
**Priority:** high

Channels:
- `issue.{issue_id}` - public chat under donation issue

Events:
- `IssueMessageSent` - new chat message

**Files:**
- `app/Events/IssueMessageSent.php`
- `app/Listeners/BroadcastIssueMessage.php`
- `routes/channels.php`

### Task 5.2: Private Chat (Reverb)
**Status:** pending  
**Priority:** high

Channels:
- `private-chat.{user_id}` - personal messages

Events:
- `PrivateMessageSent` - new private message

**Files:**
- `app/Events/PrivateMessageSent.php`
- `app/Listeners/BroadcastPrivateMessage.php`

### Task 5.3: Friendship Events
**Status:** pending  
**Priority:** medium

Channels:
- `friendship.{user_id}` - friend request notifications

Events:
- `FriendRequestSent`
- `FriendRequestAccepted`

**Files:**
- `app/Events/FriendRequestSent.php`
- `app/Events/FriendRequestAccepted.php`

---

## Phase 6: Frontend Pages

### Task 6.1: Page Structure
**Status:** pending  
**Priority:** high

Pages to create in `resources/js/pages`:
- `Home.tsx` - public donation issues feed
- `Issues/Show.tsx` - issue detail with chat
- `Issues/Create.tsx` - create new issue
- `Donations/Pledge.tsx` - pledge food to issue
- `Profile/Show.tsx` - user profile
- `Profile/Edit.tsx` - edit profile
- `Friends/Index.tsx` - friends list
- `Friends/Requests.tsx` - friend requests
- `Messages/Index.tsx` - private messages
- `Messages/Chat.tsx` - conversation view
- `Auth/Login.tsx` - login page
- `Auth/Register.tsx` - register page

### Task 6.2: Components
**Status:** pending  
**Priority:** high

Create reusable components:
- `DonationIssueCard` - issue preview card
- `DonationCard` - donation pledge card
- `ChatWindow` - real-time chat component
- `ImageUploader` - drag & drop upload
- `FriendButton` - add/remove friend
- `ShareButton` - share functionality
- `ImageGallery` - photo gallery

**Files:**
- `resources/js/Components/DonationIssueCard.tsx`
- `resources/js/Components/DonationCard.tsx`
- `resources/js/Components/ChatWindow.tsx`
- `resources/js/Components/ImageUploader.tsx`
- `resources/js/Components/FriendButton.tsx`
- `resources/js/Components/ShareButton.tsx`
- `resources/js/Components/ImageGallery.tsx`

### Task 6.3: Frontend Services
**Status:** pending  
**Priority:** medium

Implement:
- WebSocket client setup (Inertia/Reverb)
- Image upload service
- Notification service

**Files:**
- `resources/js/lib/reverb.ts`
- `resources/js/lib/upload.ts`

---

## Phase 7: Testing

### Task 7.1: Feature Tests
**Status:** pending  
**Priority:** high

Create tests:
- `DonationIssueTest` - CRUD operations
- `DonationTest` - pledge functionality
- `FriendshipTest` - friend requests
- `ImageUploadTest` - file uploads
- `ChatTest` - message sending

**Files:**
- `tests/Feature/DonationIssueTest.php`
- `tests/Feature/DonationTest.php`
- `tests/Feature/FriendshipTest.php`
- `tests/Feature/ImageUploadTest.php`
- `tests/Feature/ChatTest.php`

---

## Database Schema

```sql
-- Users (existing, extend with profile)
-- profile_photo, bio, location

-- Donation Issues
-- id, user_id, title, description, location, required_items, status, expires_at

-- Donations
-- id, user_id, donation_issue_id, amount, items_description, status

-- Images
-- id, imageable_id, imageable_type, path, type (issue|donation|profile)

-- Friendships
-- id, user_id, friend_id, status (pending|accepted), created_at

-- Issue Messages
-- id, donation_issue_id, user_id, message, created_at

-- Private Messages
-- id, sender_id, recipient_id, message, read_at, created_at
```

---

## API Endpoints Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | /api/issues | Public | List donation issues |
| GET | /api/issues/{id} | Public | Show issue details |
| POST | /api/issues | Required | Create issue (volunteer) |
| POST | /api/issues/{id}/donations | Required | Pledge donation |
| GET | /api/friends | Required | List friends |
| POST | /api/friends/{id} | Required | Send friend request |
| PUT | /api/friends/{id}/accept | Required | Accept request |
| WS | /ws | Required | WebSocket connection |