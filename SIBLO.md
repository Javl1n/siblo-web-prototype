# SIBLO Prototype - Teacher Platform Features & Context

## Project Overview

**SIBLO** is an educational RPG platform for Filipino students (grades 4-10) that gamifies learning through creature collection and evolution. Students collect and evolve creatures called "Siblons" by performing well on quizzes.

**Tagline**: "Kaalaman ang Lakas" (Knowledge is Strength)

### Core Problem Statement
- Lack of student engagement in Philippine education system
- Limited access to quality educational content
- Difficulty tracking student progress
- Teacher burden in creating assessment materials

### Value Proposition
- **For Students**: Engaging game-based learning that motivates academic achievement
- **For Teachers**: AI-powered quiz generation to reduce workload

### Prototype Scope
This prototype focuses on the **Teacher Dashboard** - the web-based platform for teachers to manage content, students, and track progress. The game client (student-facing) is a separate application.

---

## Architecture

### System Components

**This Laravel application is a unified backend serving TWO client applications:**

1. **Game Client** (PixiJS - Separate Repository)
   - Student-facing RPG interface
   - Communicates via RESTful API
   - Uses Laravel Sanctum for authentication
   - Real-time battles via Laravel Reverb (WebSocket)

2. **Teacher Dashboard** (This Repository)
   - Web-based platform using Laravel + Inertia.js + React
   - Content management and quiz creation
   - Student progress tracking and analytics
   - AI quiz generation interface
   - Uses traditional Laravel session authentication

3. **Unified Backend** (This Repository)
   - Laravel 12 application
   - RESTful API endpoints for game client
   - Web routes with Inertia for teacher dashboard
   - Shared data models and business logic
   - Real-time features with Laravel Reverb
   - Laravel Sanctum for API authentication
   - Laravel Fortify for session authentication

### Technical Stack

**Backend:**
- **Framework**: Laravel 12
- **API Auth**: Laravel Sanctum (for game client)
- **Session Auth**: Laravel Fortify (for teacher dashboard)
- **Real-time**: Laravel Reverb (WebSocket server)
- **Cache/Sessions**: Redis
- **Database**: MySQL/PostgreSQL (SQLite for development)

**Teacher Dashboard (Web):**
- **Frontend Framework**: React 19
- **SPA Framework**: Inertia.js v2
- **Styling**: Tailwind CSS v4
- **Type Safety**: Wayfinder (Laravel routes → TypeScript)
- **UI Components**: Radix UI

**Game Client (Separate):**
- **Game Engine**: PixiJS
- **API Client**: Fetch API with Sanctum tokens
- **Real-time**: Laravel Echo.js (connecting to Reverb)
- **State Management**: TBD by game team

**DevOps:**
- **Queue**: Laravel Queue with Redis driver
- **Testing**: Pest v4
- **Code Style**: Laravel Pint

---

## Prototype Strategy: Training Grounds

The prototype uses a contained "Training Grounds" demo environment rather than the full 8-region system.

### Why Training Grounds?
- Tests core quiz-to-reward-to-Siblon-growth loop
- No grade restrictions or region locking
- Validates essential mechanics before scaling
- Simpler content requirements for initial testing

### Training Grounds Features
- Basic quiz system (pool-based, no class assignments)
- Simplified Siblon collection (subset of creatures)
- Core reward mechanics
- Progress tracking fundamentals
- Server-authoritative battle system

### Simplified Prototype Architecture

**What's EXCLUDED from the prototype** (to focus on core mechanics):
- ❌ School management (no schools table)
- ❌ Class management (no classes/enrollments)
- ❌ Quiz assignments to specific classes
- ❌ Grade-level restrictions
- ❌ Parent portals
- ❌ Complex role hierarchies (just teacher vs student)

**What's INCLUDED in the prototype:**
- ✅ Teacher dashboard for quiz creation
- ✅ AI-powered quiz generation
- ✅ Pool-based quiz system (all quizzes available to all students)
- ✅ Student progress tracking (teachers see ALL students)
- ✅ Real-time battle system with server validation
- ✅ Siblon collection and evolution
- ✅ Quiz-to-reward-to-growth loop
- ✅ API for game client integration

---

## Teacher Dashboard Features

### 1. Authentication & User Management

#### User Roles
- **Teacher**: Standard teacher account
- **Admin**: School administrator with elevated permissions

#### Authentication Features
- Login/Logout
- Password reset
- Session management
- Role-based access control

#### Teacher Profile
- Name and contact information
- School affiliation
- Subjects taught
- Class assignments

---

### 2. Student Management (Simplified for Prototype)

**Note:** In the prototype, there are NO classes or schools. Teachers see ALL students globally.

#### All Students View
- List all registered students on the platform
- Search and filter students by name, grade level
- Basic student information
- Individual student progress overview
- View student's Siblon collection (read-only)

#### Individual Student Details
- Quiz completion history
- Performance metrics
- Siblon collection progress
- Learning trends and patterns

**Post-Prototype:** Full class management will be added with schools, class rosters, and enrollment tracking.

---

### 3. Quiz Management System

This is the core feature for the prototype.

#### Quiz Creation
**Manual Quiz Creation**
- Quiz title and description
- Subject and topic selection
- Difficulty level (Easy, Medium, Hard)
- Grade level targeting
- Number of questions
- Time limit (optional)
- Pass threshold percentage

**Question Types**
- Multiple choice (4 options)
- True/False
- Fill in the blank
- Multiple correct answers

**Question Editor**
- Question text with rich text support
- Answer options
- Correct answer marking
- Explanation for correct answer
- Point value per question
- Media attachments (images)

#### AI Quiz Generation
**Core Feature**: Reduce teacher workload through AI-powered content creation

**Input Parameters**
- Subject (Math, Science, English, Filipino, etc.)
- Topic/Chapter
- Grade level
- Difficulty level
- Number of questions
- Specific learning competencies (optional)
- Keywords or focus areas

**Generation Process**
1. Teacher provides parameters
2. AI generates questions with multiple choice options
3. Teacher reviews generated content
4. Teacher can edit individual questions
5. Teacher approves or regenerates
6. Quiz is saved to question bank

**AI Features**
- Culturally relevant content (Philippine context)
- Aligned with DepEd curriculum
- Age-appropriate language
- Varied question difficulty within quiz
- Explanation generation for answers

#### Quiz Publishing (Pool-Based System)
**Note:** In the prototype, quizzes are NOT assigned to specific classes. Once published, quizzes are available to ALL students.

- Publish/unpublish quizzes
- Set quiz visibility (published = available to all students)
- Set maximum attempts allowed
- Optional: Set due date for tracking purposes
- Mark quiz as featured/recommended

**Post-Prototype:** Quiz assignments to specific classes will be added.

#### Question Bank
- Library of all created questions
- Filter by subject, topic, grade, difficulty
- Reuse questions across multiple quizzes
- Tag system for organization
- Search functionality

---

### 4. Progress Tracking & Analytics

#### Individual Student Analytics
- Quiz completion rate
- Average scores by subject
- Performance trends over time
- Time spent on quizzes
- Strongest/weakest topics
- Siblon collection progress (read-only view)

#### Class Analytics
- Class average scores
- Quiz completion rates
- Performance comparison across topics
- Identification of struggling students
- Subject-wise performance breakdown

#### Quiz Analytics
- Overall difficulty assessment
- Question-level statistics
  - % of students who got each question correct
  - Most commonly selected wrong answers
  - Average time per question
- Pass/fail rates
- Score distribution

#### Reports
- Exportable reports (PDF/CSV)
- Custom date ranges
- Filtering by class, student, subject
- Performance summaries
- Progress reports for parents

---

### 5. Content Management

#### Curriculum Alignment
- DepEd K-12 curriculum framework
- Subject competencies listing
- Learning objectives mapping
- Grade level standards

#### Topic Organization
- Hierarchical topic structure
- Subject → Grade → Quarter → Topic
- Tags and keywords
- Difficulty progression

---

### 6. Dashboard Home

#### Teacher Overview
- Quick stats (total students, active classes, pending quizzes)
- Recent activity feed
- Upcoming quiz deadlines
- Alert notifications (students needing attention)

#### Quick Actions
- Create new quiz
- Generate AI quiz
- View recent class performance
- Check student submissions

---

## Database Schema (Simplified for Prototype)

**Note:** Schools, Classes, Class_Enrollments, and Quiz_Assignments tables are NOT included in the prototype.

### User Management Tables

#### users
- `id` (Primary Key)
- `name` (Full name)
- `email` (Unique)
- `username` (Unique, 50 chars)
- `password` (Hashed)
- `user_type` (Enum: 'student', 'teacher')
- `email_verified_at` (Nullable timestamp)
- `two_factor_secret` (Nullable, encrypted)
- `two_factor_recovery_codes` (Nullable, encrypted)
- `two_factor_confirmed_at` (Nullable timestamp)
- `remember_token`
- `created_at`
- `updated_at`

#### teacher_profiles
- `id` (Primary Key)
- `user_id` (Foreign Key → users)
- `specialization` (Nullable, e.g., "Math", "Science")
- `bio` (Nullable text)
- `created_at`
- `updated_at`

#### player_profiles (Student profiles)
- `id` (Primary Key)
- `user_id` (Foreign Key → users)
- `trainer_name` (Display name in game, 50 chars)
- `level` (Integer, default 1)
- `experience_points` (Integer, default 0)
- `coins` (Integer, default 0)
- `current_region_id` (Nullable)
- `created_at`
- `updated_at`

---

### Quiz System Tables

#### quizzes
- `id` (Primary Key)
- `teacher_id` (Foreign Key → users)
- `title` (String, 255 chars)
- `description` (Text, nullable)
- `subject` (String, e.g., "Math", "Science")
- `topic` (String, nullable)
- `difficulty_level` (Enum: 'easy', 'medium', 'hard')
- `time_limit_minutes` (Integer, nullable)
- `pass_threshold` (Integer, default 60 - percentage)
- `max_attempts` (Integer, nullable - null = unlimited)
- `is_published` (Boolean, default false)
- `is_featured` (Boolean, default false)
- `is_generated_by_ai` (Boolean, default false)
- `ai_generation_id` (Foreign Key → ai_quiz_generations, nullable)
- `created_at`
- `updated_at`

#### questions
- `id` (Primary Key)
- `quiz_id` (Foreign Key → quizzes, cascades on delete)
- `question_text` (Text)
- `question_type` (Enum: 'multiple_choice', 'true_false', 'fill_blank', 'multiple_correct')
- `points` (Integer, default 1)
- `order_index` (Integer)
- `explanation` (Text, nullable)
- `media_url` (String, nullable)
- `created_at`
- `updated_at`

#### question_choices
- `id` (Primary Key)
- `question_id` (Foreign Key → questions, cascades on delete)
- `choice_text` (String, 500 chars)
- `is_correct` (Boolean)
- `order_index` (Integer)
- `created_at`
- `updated_at`

#### quiz_attempts
- `id` (Primary Key)
- `quiz_id` (Foreign Key → quizzes)
- `student_id` (Foreign Key → users)
- `started_at` (Timestamp)
- `submitted_at` (Timestamp, nullable)
- `score` (Integer, nullable - total points earned)
- `max_score` (Integer - total possible points)
- `percentage` (Decimal, nullable)
- `time_taken_seconds` (Integer, nullable)
- `attempt_number` (Integer - nth attempt for this quiz by this student)
- `is_completed` (Boolean, default false)
- `created_at`
- `updated_at`

#### quiz_attempt_answers
- `id` (Primary Key)
- `quiz_attempt_id` (Foreign Key → quiz_attempts, cascades on delete)
- `question_id` (Foreign Key → questions)
- `answer_given` (Text - stores student's answer)
- `selected_choice_ids` (JSON, nullable - for multiple choice questions)
- `is_correct` (Boolean)
- `points_earned` (Integer)
- `time_spent_seconds` (Integer, nullable)
- `created_at`
- `updated_at`

#### quiz_rewards
- `id` (Primary Key)
- `quiz_attempt_id` (Foreign Key → quiz_attempts)
- `student_id` (Foreign Key → users)
- `experience_points` (Integer)
- `coins` (Integer)
- `reward_data` (JSON - additional rewards like items, Siblons)
- `awarded_at` (Timestamp)
- `created_at`
- `updated_at`

#### ai_quiz_generations
- `id` (Primary Key)
- `teacher_id` (Foreign Key → users)
- `quiz_id` (Foreign Key → quizzes, nullable until approved)
- `input_parameters` (JSON - subject, topic, difficulty, etc.)
- `generated_content` (JSON - AI-generated questions)
- `status` (Enum: 'pending', 'approved', 'rejected', 'regenerated')
- `ai_model` (String - which AI model was used)
- `generation_time_seconds` (Integer, nullable)
- `approved_at` (Timestamp, nullable)
- `created_at`
- `updated_at`

#### teacher_quiz_analytics
- `id` (Primary Key)
- `quiz_id` (Foreign Key → quizzes)
- `total_attempts` (Integer)
- `completed_attempts` (Integer)
- `average_score` (Decimal)
- `average_time_seconds` (Integer)
- `pass_rate` (Decimal)
- `last_calculated_at` (Timestamp)
- `created_at`
- `updated_at`

---

### Game Mechanics Tables

#### siblon_species
- `id` (Primary Key)
- `name` (String, 100 chars)
- `dex_number` (Integer, unique - Pokédex-style number)
- `type_primary` (String - e.g., "Fire", "Water")
- `type_secondary` (String, nullable)
- `rarity` (Enum: 'common', 'uncommon', 'rare', 'legendary')
- `base_hp` (Integer)
- `base_attack` (Integer)
- `base_defense` (Integer)
- `base_speed` (Integer)
- `evolution_level` (Integer, nullable)
- `evolves_to_species_id` (Foreign Key → siblon_species, nullable)
- `region` (String, nullable)
- `sprite_url` (String, nullable)
- `description` (Text)
- `created_at`
- `updated_at`

#### player_siblons
- `id` (Primary Key)
- `player_id` (Foreign Key → users)
- `species_id` (Foreign Key → siblon_species)
- `nickname` (String, nullable, 50 chars)
- `level` (Integer, default 1)
- `experience_points` (Integer, default 0)
- `current_hp` (Integer)
- `max_hp` (Integer)
- `attack_stat` (Integer)
- `defense_stat` (Integer)
- `speed_stat` (Integer)
- `is_in_party` (Boolean, default false)
- `caught_at` (Timestamp)
- `created_at`
- `updated_at`

#### siblon_evolutions
- `id` (Primary Key)
- `player_siblon_id` (Foreign Key → player_siblons)
- `from_species_id` (Foreign Key → siblon_species)
- `to_species_id` (Foreign Key → siblon_species)
- `evolved_at` (Timestamp)
- `trigger_type` (String - e.g., "level_up", "quiz_mastery")
- `created_at`
- `updated_at`

#### siblon_level_ups
- `id` (Primary Key)
- `player_siblon_id` (Foreign Key → player_siblons)
- `from_level` (Integer)
- `to_level` (Integer)
- `experience_gained` (Integer)
- `stat_increases` (JSON - HP, attack, defense, speed increases)
- `leveled_up_at` (Timestamp)
- `created_at`
- `updated_at`

---

### Battle System Tables

#### battle_states
- `id` (Primary Key)
- `battle_id` (String, UUID - unique battle identifier)
- `player1_id` (Foreign Key → users)
- `player2_id` (Foreign Key → users, nullable - null for AI battles)
- `player1_siblon_id` (Foreign Key → player_siblons)
- `player2_siblon_id` (Foreign Key → player_siblons, nullable)
- `current_turn` (Integer, default 1)
- `turn_player_id` (Foreign Key → users - whose turn it is)
- `player1_hp` (Integer)
- `player2_hp` (Integer)
- `battle_type` (Enum: 'pvp', 'pve', 'training')
- `status` (Enum: 'active', 'completed', 'abandoned')
- `winner_id` (Foreign Key → users, nullable)
- `battle_log` (JSON - complete move history)
- `started_at` (Timestamp)
- `completed_at` (Timestamp, nullable)
- `created_at`
- `updated_at`

#### daily_activities
- `id` (Primary Key)
- `player_id` (Foreign Key → users)
- `activity_date` (Date)
- `quizzes_completed` (Integer, default 0)
- `experience_gained` (Integer, default 0)
- `battles_won` (Integer, default 0)
- `battles_lost` (Integer, default 0)
- `login_streak` (Integer, default 0)
- `created_at`
- `updated_at`

---

### Tables Excluded from Prototype
**These will be added post-prototype:**
- `schools` - School information
- `classes` - Teacher's classes
- `class_enrollments` - Student-class relationships
- `quiz_assignments` - Assigning quizzes to specific classes with due dates

---

## Battle System Architecture

### Strategy: Server as Referee (Server-Authoritative)

The battle system uses a **server-authoritative architecture** where clients send move intentions and the backend calculates all damage, HP changes, and battle outcomes. This prevents cheating while maintaining reasonable latency for turn-based battles.

### Battle Flow Diagram

```
Player A (PixiJS)              Laravel Backend              Player B (PixiJS)
      |                              |                              |
      |----[POST /api/battles/start]--->|                              |
      |                              |                              |
      |<------[Battle Created]-------|-------[Broadcast: BattleStarted]->|
      |                              |                              |
      |                         [Creates battle                        |
      |                          state in Redis]                       |
      |                              |                              |
      |--[Whisper: move-intent]----->|                              |
      |                              |                              |
      |                      [Calculate damage]                       |
      |                       [Update HP in Redis]                    |
      |                              |                              |
      |<-[Broadcast: MoveExecuted]---|--[Broadcast: MoveExecuted]-->|
      |                              |                              |
   [Animate]                    [Check for KO]                   [Animate]
      |                              |                              |
      |                         [If battle ends:]                     |
      |                       [Calculate rewards]                     |
      |                         [Update player                        |
      |                           profiles & DB]                      |
      |                              |                              |
      |<--[Broadcast: BattleEnded]---|--[Broadcast: BattleEnded]--->|
```

### How It Works

#### 1. Battle Initiation
```javascript
// Game Client (PixiJS)
const response = await fetch('/api/battles/start', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + sanctumToken,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    player_siblon_id: 42,
    opponent_type: 'pvp' // or 'pve' for AI
  })
});

const { battle_id, initial_state } = await response.json();
```

```php
// Backend: BattleController
public function start(Request $request) {
    $battleId = Str::uuid();
    $playerSiblon = PlayerSiblon::find($request->player_siblon_id);

    // Create battle state in Redis (fast access)
    $battleState = BattleState::create([
        'battle_id' => $battleId,
        'player1_id' => $request->user()->id,
        'player1_siblon_id' => $playerSiblon->id,
        'player1_hp' => $playerSiblon->current_hp,
        'player2_hp' => $opponentHp,
        'current_turn' => 1,
        'status' => 'active'
    ]);

    // Broadcast to both players
    broadcast(new BattleStarted($battleState));

    return response()->json($battleState);
}
```

#### 2. Move Execution (Server-Authoritative)

```javascript
// Game Client: Player selects move
Echo.private(`battle.${battleId}`)
  .whisper('move-intent', {
    player_id: playerId,
    move_id: 'tackle',
    target_siblon_id: opponentSiblonId
  });
```

```php
// Backend: Listen for move intentions
class ProcessBattleMove implements ShouldQueue {
    public function handle(BattleMoveIntent $event) {
        $battle = BattleState::find($event->battleId);

        // Get attacker/defender stats from database
        $attacker = PlayerSiblon::find($event->attackerSiblonId);
        $defender = PlayerSiblon::find($event->defenderSiblonId);

        // Server calculates damage (authoritative)
        $damage = $this->calculateDamage(
            $attacker->attack_stat,
            $defender->defense_stat,
            $event->moveId,
            $this->calculateRandomMultiplier()
        );

        // Update battle state in Redis
        $newHp = max(0, $battle->getDefenderHp() - $damage);
        $battle->applyDamage($event->defenderId, $damage);
        $battle->incrementTurn();

        // Check for knockout
        $isKnockedOut = $newHp <= 0;

        if ($isKnockedOut) {
            $this->endBattle($battle, $event->attackerId);
        }

        // Broadcast authoritative result
        broadcast(new BattleMoveExecuted([
            'battle_id' => $battle->battle_id,
            'attacker_id' => $event->attackerId,
            'defender_id' => $event->defenderId,
            'move' => $event->moveId,
            'damage' => $damage,
            'defender_new_hp' => $newHp,
            'is_knockout' => $isKnockedOut,
            'current_turn' => $battle->current_turn
        ]));
    }

    private function calculateDamage(int $attack, int $defense, string $moveId, float $randomMultiplier): int {
        $move = MoveDatabase::get($moveId);
        $baseDamage = $move['power'];

        // Damage formula (similar to Pokémon)
        $damage = (($attack / $defense) * $baseDamage * $randomMultiplier) / 10;

        return max(1, (int) round($damage));
    }
}
```

```javascript
// Game Client: Receive authoritative results
Echo.private(`battle.${battleId}`)
  .listen('BattleMoveExecuted', (result) => {
    // Update local state with server's calculation
    updateSiblonHP(result.defender_id, result.defender_new_hp);

    // Play battle animations
    animateAttack(result.attacker_id, result.defender_id, result.move);
    displayDamage(result.damage);

    if (result.is_knockout) {
      showKnockoutAnimation(result.defender_id);
    }
  });
```

#### 3. Battle Conclusion

```php
// Backend: End battle and award rewards
private function endBattle(BattleState $battle, int $winnerId) {
    $battle->update([
        'status' => 'completed',
        'winner_id' => $winnerId,
        'completed_at' => now()
    ]);

    // Calculate rewards based on battle outcome
    $rewards = $this->calculateRewards($battle);

    // Update winner's profile
    $winner = User::find($winnerId);
    $winner->playerProfile->addExperience($rewards['xp']);
    $winner->playerProfile->addCoins($rewards['coins']);

    // Persist battle to database
    $battle->persistToDatabase();

    // Broadcast completion
    broadcast(new BattleEnded([
        'battle_id' => $battle->battle_id,
        'winner_id' => $winnerId,
        'rewards' => $rewards
    ]));
}
```

### Why This Approach?

#### ✅ Advantages
- **Cheat-Proof**: All damage calculations happen on server, impossible to fake HP
- **Consistent**: Both clients always see the same battle state
- **Fair**: No client-side manipulation of stats or moves
- **Debuggable**: Complete battle log stored server-side
- **Scalable**: Redis handles fast state access, events are queued

#### ❌ Tradeoffs
- **Latency**: ~100-300ms per move (acceptable for turn-based)
- **Server Load**: Backend processes every move
- **Complexity**: More code than pure client-side battles

### Battle State Management

**During Active Battles:**
- State stored in **Redis** for fast access
- TTL: 30 minutes (abandoned battles auto-expire)

**After Battle Completion:**
- State persisted to **PostgreSQL/MySQL**
- Used for analytics and replay features

---

## API Endpoints for Game Client

The PixiJS game client communicates with the backend via RESTful API endpoints. All endpoints require **Laravel Sanctum** authentication.

### Authentication

#### POST /api/auth/register
Register a new student account.

**Request:**
```json
{
  "name": "Juan Dela Cruz",
  "username": "juandc",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "user_type": "student",
  "trainer_name": "Trainer Juan"
}
```

**Response:**
```json
{
  "user": { "id": 1, "username": "juandc", "user_type": "student" },
  "token": "1|sanctum_token_here"
}
```

#### POST /api/auth/login
Login and receive Sanctum token.

**Request:**
```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": { "id": 1, "username": "juandc" },
  "token": "2|sanctum_token_here"
}
```

#### POST /api/auth/logout
Revoke current token.

---

### Player Profile

#### GET /api/player/profile
Get current player's profile and stats.

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "trainer_name": "Trainer Juan",
  "level": 15,
  "experience_points": 3450,
  "coins": 1200,
  "current_region_id": 1
}
```

#### GET /api/player/siblons
Get player's Siblon collection.

**Response:**
```json
{
  "party": [
    {
      "id": 42,
      "species_id": 1,
      "nickname": "Flamey",
      "level": 18,
      "current_hp": 45,
      "max_hp": 50,
      "attack_stat": 35,
      "defense_stat": 28,
      "speed_stat": 40,
      "is_in_party": true
    }
  ],
  "collection": [ /* all owned Siblons */ ]
}
```

---

### Quizzes

#### GET /api/quizzes
Get all published quizzes available to students.

**Query Parameters:**
- `difficulty` (optional): easy, medium, hard
- `subject` (optional): Math, Science, etc.

**Response:**
```json
{
  "quizzes": [
    {
      "id": 10,
      "title": "Grade 5 Math - Fractions",
      "description": "Test your knowledge of fractions",
      "subject": "Math",
      "difficulty_level": "medium",
      "time_limit_minutes": 15,
      "max_attempts": 3,
      "question_count": 10,
      "is_featured": true
    }
  ]
}
```

#### GET /api/quizzes/{id}
Get quiz details with questions (when student starts quiz).

**Response:**
```json
{
  "id": 10,
  "title": "Grade 5 Math - Fractions",
  "time_limit_minutes": 15,
  "questions": [
    {
      "id": 101,
      "question_text": "What is 1/2 + 1/4?",
      "question_type": "multiple_choice",
      "points": 1,
      "choices": [
        { "id": 1, "choice_text": "3/4", "order_index": 0 },
        { "id": 2, "choice_text": "2/6", "order_index": 1 },
        { "id": 3, "choice_text": "1/6", "order_index": 2 },
        { "id": 4, "choice_text": "3/6", "order_index": 3 }
      ]
    }
  ]
}
```

#### POST /api/quizzes/{id}/start
Start a quiz attempt.

**Response:**
```json
{
  "attempt_id": 500,
  "quiz_id": 10,
  "started_at": "2025-11-10T10:30:00Z",
  "expires_at": "2025-11-10T10:45:00Z"
}
```

#### POST /api/quiz-attempts/{id}/submit
Submit quiz answers.

**Request:**
```json
{
  "answers": [
    { "question_id": 101, "selected_choice_ids": [1] },
    { "question_id": 102, "selected_choice_ids": [2, 3] }
  ]
}
```

**Response:**
```json
{
  "score": 8,
  "max_score": 10,
  "percentage": 80,
  "passed": true,
  "rewards": {
    "experience_points": 150,
    "coins": 50,
    "items": []
  },
  "answers": [
    {
      "question_id": 101,
      "is_correct": true,
      "points_earned": 1,
      "explanation": "1/2 + 1/4 = 2/4 + 1/4 = 3/4"
    }
  ]
}
```

---

### Battles

#### POST /api/battles/start
Initiate a new battle.

**Request:**
```json
{
  "player_siblon_id": 42,
  "battle_type": "pvp",
  "opponent_id": 5
}
```

**Response:**
```json
{
  "battle_id": "550e8400-e29b-41d4-a716-446655440000",
  "player1": {
    "user_id": 1,
    "siblon_id": 42,
    "hp": 50,
    "max_hp": 50
  },
  "player2": {
    "user_id": 5,
    "siblon_id": 78,
    "hp": 45,
    "max_hp": 45
  },
  "current_turn": 1,
  "turn_player_id": 1
}
```

#### WebSocket: battle.{battleId}
Subscribe to battle channel for real-time updates.

**Events Received:**
- `BattleStarted`
- `BattleMoveExecuted` (contains damage, HP, knockout status)
- `BattleEnded` (contains winner, rewards)

**Whisper Events Sent:**
- `move-intent` - Client sends move selection to backend

#### GET /api/battles/{id}
Get current battle state.

#### POST /api/battles/{id}/forfeit
Forfeit an active battle.

---

### Daily Activities & Stats

#### GET /api/player/daily-activity
Get today's activity summary.

**Response:**
```json
{
  "activity_date": "2025-11-10",
  "quizzes_completed": 3,
  "experience_gained": 450,
  "battles_won": 2,
  "battles_lost": 1,
  "login_streak": 7
}
```

---

### API Documentation Strategy

**For the game development team:**

1. **OpenAPI/Swagger Documentation**
   - Auto-generated from Laravel routes
   - Accessible at `/api/documentation`
   - Exportable as JSON for import into API clients

2. **Setup Instructions:**
   ```bash
   composer require darkaonline/l5-swagger
   php artisan l5-swagger:generate
   ```

3. **Generated TypeScript Types** (Optional)
   - Use a shared npm package `@siblo/api-types`
   - Both backend and game client import same interfaces
   - Ensures type safety across both codebases

4. **Example API Client (Game Side):**
   ```typescript
   // siblo-api-client.ts
   class SibloAPI {
     private token: string;
     private baseURL = 'https://api.siblo.com';

     async getQuizzes(filters?: QuizFilters): Promise<Quiz[]> {
       const response = await fetch(`${this.baseURL}/api/quizzes`, {
         headers: { 'Authorization': `Bearer ${this.token}` }
       });
       return response.json();
     }

     async startBattle(siblonId: number): Promise<Battle> {
       const response = await fetch(`${this.baseURL}/api/battles/start`, {
         method: 'POST',
         headers: {
           'Authorization': `Bearer ${this.token}`,
           'Content-Type': 'application/json'
         },
         body: JSON.stringify({ player_siblon_id: siblonId })
       });
       return response.json();
     }
   }
   ```

---

## Game Mechanics Context (Read-Only for Teachers)

Teachers can view but not directly control these game elements:

### Siblons
- 182 species across 8 regions (prototype will use subset)
- Three evolution types
- Stats system: Base stats + Learning Points from quizzes
- Subject affinity system

### Progression System
- Quiz performance → Learning Points
- Learning Points → Siblon evolution
- Subject-specific bonuses
- Collection completion rewards

### Regional Structure (Full Game - Not in Prototype)
- 8 Philippine-inspired regions
- Each region = grade level
- Region-specific Siblons
- Progressive difficulty

---

## Development Phases

### Phase 1: Foundation & Core Backend (Current)
**Goal:** Database, authentication, and basic API infrastructure

✅ **Completed:**
- Database schema design (simplified for prototype)
- User authentication (Fortify for web, Sanctum for API)
- Basic user models (User, PlayerProfile, TeacherProfile)
- Game mechanics models (Siblons, Species, Evolutions)
- Quiz models (Quiz, Question, QuestionChoice, QuizAttempt)

🔨 **In Progress:**
- Teacher dashboard UI layout
- Quiz CRUD controllers and API endpoints
- Student management views (global list)
- Battle system foundation

📋 **TODO:**
- Fix user registration (capture username, user_type)
- Add missing quiz fields (subject, topic, is_published)
- Battle state management (Redis)
- API routes for game client

---

### Phase 2: Teacher Dashboard & Quiz Management
**Goal:** Full quiz creation and management for teachers

- Quiz creation UI (manual)
  - Rich text editor for questions
  - Multiple question types (multiple choice, true/false, fill in blank)
  - Media upload for question images
  - Quiz preview functionality
- Question bank interface
  - Search and filter
  - Tag system
  - Reuse questions across quizzes
- Quiz publishing system (pool-based)
  - Publish/unpublish quizzes
  - Feature quizzes
  - Set difficulty and metadata
- Student management dashboard
  - View all students
  - Individual student progress
  - Quiz completion tracking

---

### Phase 3: Game Client API & Battle System
**Goal:** Complete API for PixiJS game client

- Sanctum authentication endpoints
- Quiz API endpoints
  - List published quizzes
  - Start quiz attempt
  - Submit answers and get rewards
- Player profile API
  - Get profile and stats
  - Siblon collection endpoints
- Battle system implementation
  - Server-authoritative battle logic
  - Redis battle state management
  - Reverb WebSocket integration
  - Move validation and damage calculation
  - Battle rewards calculation
- API documentation (OpenAPI/Swagger)

---

### Phase 4: AI Quiz Generation
**Goal:** AI-powered quiz creation to reduce teacher workload

- AI service integration (OpenAI/Anthropic/Claude)
- Generation parameter interface
  - Subject, topic, difficulty selection
  - Number of questions
  - DepEd curriculum alignment
  - Philippine cultural context
- Review and edit workflow
  - Preview generated questions
  - Edit individual questions
  - Approve or regenerate
- Save to question bank
- Track AI generation history

---

### Phase 5: Analytics & Reporting
**Goal:** Teacher insights and student progress tracking

- Teacher analytics dashboard
  - Overall stats (students, quizzes, completions)
  - Quiz performance metrics
  - Student engagement trends
- Individual student analytics
  - Quiz completion rate
  - Performance by subject
  - Strengths and weaknesses
  - Learning trends over time
- Quiz-level analytics
  - Question difficulty analysis
  - Common wrong answers
  - Pass/fail rates
  - Time spent per question
- Export functionality
  - PDF/CSV reports
  - Custom date ranges
  - Filtering options

---

### Phase 6: Polish & User Testing
**Goal:** Prepare for prototype demonstration

- UI/UX improvements
- Performance optimization
- Bug fixes and edge cases
- User testing with teachers
- Game client integration testing
- Documentation finalization
- Deployment setup

---

### Post-Prototype: Full Feature Set
**To be added after prototype validation:**

- School management system
- Class and enrollment management
- Quiz assignments to specific classes
- Parent portal for progress viewing
- Mobile app for teacher dashboard
- Advanced AI features (adaptive difficulty)
- Collaborative quiz creation
- Marketplace for sharing quiz content
- Integration with school LMS systems
- Offline mode for low-connectivity areas

---

## Key Design Principles

### Educational Integrity
- Academic achievement drives game progression
- No pay-to-win mechanics
- Content aligned with DepEd curriculum
- Age-appropriate content

### Teacher-Centric Design
- Reduce workload through AI
- Intuitive content creation
- Actionable analytics
- Time-saving workflows

### Scalability
- Designed for school-wide deployment
- Handles multiple classes per teacher
- Bulk operations where needed
- Efficient data management

### Separation of Concerns
- Teacher dashboard independent from game client
- Shared backend ensures data consistency
- Different interfaces for different user types
- Clear API boundaries

---

## Business Context

### Target Market
- **Primary**: Private schools in South Cotabato (B2B)
- **Secondary**: Public schools (B2G)

### Business Model
- School licensing (per-student or per-school)
- Freemium tier (limited features)
- Premium tier (full AI generation, advanced analytics)

### Competitive Advantages
- Cultural relevance (Philippine context)
- AI-powered content generation
- Gamification that actually drives learning
- Comprehensive analytics

---

## User Flows

### Quiz Creation Flow (Manual)
1. Teacher logs in
2. Navigate to "Create Quiz"
3. Enter quiz metadata (title, subject, grade, etc.)
4. Add questions one by one
5. Set correct answers and explanations
6. Preview quiz
7. Save to question bank
8. Assign to classes

### Quiz Creation Flow (AI-Generated)
1. Teacher logs in
2. Navigate to "Generate Quiz with AI"
3. Enter generation parameters
4. Click "Generate"
5. Review generated questions
6. Edit questions as needed (or regenerate)
7. Approve and save
8. Assign to classes

### Progress Review Flow
1. Teacher logs in
2. Navigate to "Analytics" or specific class
3. Select time period
4. View class or individual student metrics
5. Drill down into specific quizzes
6. Identify struggling students/topics
7. Export reports if needed

---

## Success Metrics (Prototype)

### Teacher Adoption
- Time to create first quiz
- Number of AI-generated quizzes vs manual
- Active teachers per week
- Quiz assignment frequency

### Content Quality
- Teacher satisfaction with AI-generated content
- Edit rate on AI questions
- Reuse rate from question bank

### Student Engagement (measured but not controlled)
- Quiz completion rates
- Average scores
- Time spent on platform

---

## Future Considerations (Post-Prototype)

- Parent portal for progress viewing
- Mobile app for teacher dashboard
- Advanced AI features (adaptive difficulty)
- Collaborative quiz creation between teachers
- Marketplace for sharing quiz content
- Integration with school LMS systems
- Offline mode for areas with poor connectivity

---

## Technical Considerations

### Security
- Role-based access control
- Secure password hashing
- Session management
- API authentication tokens
- Data privacy compliance (PH Data Privacy Act)

### Performance
- Efficient database queries
- Caching for analytics
- Lazy loading for large datasets
- Optimized media delivery

### Accessibility
- Responsive design for various devices
- Screen reader compatibility
- Keyboard navigation
- Color contrast compliance

---

## Appendix: Philippine Education Context

### DepEd K-12 Program
- Kindergarten + 12 years basic education
- Grade levels: 1-10 (Primary and Junior High School)
- Core subjects: Math, Science, English, Filipino, Araling Panlipunan

### Regional Variation
- 17 regions in Philippines
- Mix of urban and rural schools
- Varying levels of internet connectivity
- Multilingual student populations

### Assessment Culture
- Heavy emphasis on standardized testing
- Quarterly examinations
- Performance tasks and projects
- Need for formative assessment tools

---

*Document Version: 2.0*
*Last Updated: November 10, 2025*
*Project: SIBLO Educational RPG Platform*
*Focus: Teacher Dashboard Prototype (Simplified Architecture)*

**Major Changes in v2.0:**
- Removed class/school management for prototype
- Added unified backend architecture (serves both dashboard + game client)
- Added server-authoritative battle system documentation
- Added complete API endpoint reference for game client
- Updated database schema (removed schools, classes, class_enrollments, quiz_assignments)
- Clarified pool-based quiz system
- Added Laravel Sanctum + Reverb technical stack details