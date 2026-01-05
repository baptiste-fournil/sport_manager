# Sport Training App – Implementation Plan

## App Summary

This application is a **sport training and performance tracking platform** designed for individual athletes (and later coaches). It allows users to:

- Create and manage an **exercise catalog**
- Build **training templates** (planned workouts)
- Start and run **training sessions** based on those templates or freestyle
- Log **performances** (sets, reps, weight, time, distance, etc.) during a session
- **Track rest/break time** between sets
- Review session history and **analyze performance progression over time**

The app is built with:
- **Laravel** (backend, API, domain logic)
- **Inertia.js + Vue 3** (frontend)
- **Tailwind CSS** (styling)
- **SQL database (PostgreSQL recommended)** for relational integrity and analytics

The architecture separates **planned training (templates)** from **performed training (sessions)**, enabling accurate tracking, comparisons, and long-term analytics.

---

## Phase 0 — Foundations (project setup)
**Goal:** stable base, auth, UI kit, dev workflow.

- Create Laravel app
- Install & configure:
  - Inertia.js + Vue 3
  - Tailwind CSS
  - Laravel Breeze (Inertia + Vue) *or* Jetstream (if teams later)
- Set up:
  - ESLint + Prettier
  - Laravel Pint
  - PHPStan (optional)
  - Vite aliases & structure
- Define conventions:
  - Route naming (`trainings.*`, `sessions.*`, etc.)
  - Page and component structure
- CI (optional): GitHub Actions (tests + lint)

**Deliverables**
- Authentication
- Dashboard
- Base UI components (Button, Input, Card, Modal, Toast)

---

## Phase 1 — Data model & migrations (V1 schema)
**Goal:** persist core concepts: exercises, trainings, sessions, performance, rest.

### Tables (V1)
- `exercises`
- `trainings`
- `training_exercises`
- `training_sessions`
- `session_exercises`
- `session_sets` (includes `rest_seconds_actual`)

Indexes & constraints:
- `training_exercises(training_id, order_index)`
- `session_exercises(session_id, order_index)`
- `session_sets(session_exercise_id, set_index)`
- `training_sessions(user_id, started_at)`

Eloquent relationships:
- User → Trainings, Exercises, Sessions
- Training → TrainingExercises
- TrainingExercise → Exercise
- Session → SessionExercises → SessionSets

**Deliverables**
- Migrations
- Models
- Factories (optional)

---

## Phase 2 — Exercise Catalog (CRUD)
**Goal:** reusable exercises.

### Backend
- `ExerciseController` CRUD
- Policies (user-owned exercises, optional global ones)
- Validation (name required, unique per user)

### Frontend
- `Exercises/Index.vue` (list + search)
- `Exercises/Form.vue` (create/edit)
- Quick-create modal

**Deliverables**
- Full exercise CRUD

---

## Phase 3 — Training Templates (Builder)
**Goal:** build planned workouts.

### Backend
- `TrainingController` CRUD
- `TrainingExerciseController` (attach, reorder, remove)
- Fields: order, default rest, notes

### Frontend
- `Trainings/Index.vue`
- `Trainings/Show.vue` (builder)
- Features:
  - add exercises from catalog
  - drag & drop reorder
  - set default rest per exercise

**Deliverables**
- Fully functional training builder

---

## Phase 4 — Start a Session
**Goal:** instantiate a real workout.

### Backend
- `TrainingSessionController@createFromTraining`
- Transaction:
  - create session
  - copy training exercises → session exercises
- Support free sessions (no template)

### Frontend
- `Sessions/Start.vue` (select training or blank)
- Redirect to live session view

**Deliverables**
- Reliable session creation flow

---

## Phase 5 — Live Session (logging + rest timer)
**Goal:** log performance and rest time.

### Core logic
- Add sets dynamically
- Complete set → start rest timer
- Start next set → compute rest duration

### Backend
- `SessionExerciseController`
- `SessionSetController`
- Store:
  - reps / weight / duration / distance
  - `completed_at`
  - `rest_seconds_actual`

### Frontend
- `Sessions/Live.vue`
- Components:
  - Exercise accordion
  - Set table
  - RestTimer component
- Use partial reloads / small JSON endpoints

**Deliverables**
- Smooth live workout experience

---

## Phase 6 — Session Review & History
**Goal:** review, edit, and browse sessions.

### Backend
- `TrainingSessionController@index`
- `TrainingSessionController@show`
- Edit sets after session (policies)

### Frontend
- `Sessions/Index.vue`
- `Sessions/Show.vue`

**Deliverables**
- Session history and summaries

---

## Phase 7 — Performance Tracking (V1 analytics)
**Goal:** visualize progress.

### Metrics (initial)
- Max weight by date
- Total volume per session
- Average rest time

### Backend
- `ExerciseStatsController`
- SQL aggregates (`group by`, date buckets)

### Frontend
- `Progress/Exercise.vue`
- Charts (Chart.js or ECharts)
- Date filters

**Deliverables**
- Basic progress charts

---

## Phase 8 — Quality, security & scaling
**Goal:** production readiness.

- Authorization policies everywhere
- Form Request validation
- Feature tests (session flow, rest calculation)
- Performance tuning (indexes, eager loading)
- Observability (Telescope, Sentry)

---

## Suggested Folder Structure

### Backend
- Controllers
  - Exercises, Trainings, TrainingExercises
  - TrainingSessions, SessionExercises, SessionSets
  - Stats
- Requests
- Policies

### Frontend
- `Pages/Exercises/*`
- `Pages/Trainings/*`
- `Pages/Sessions/*`
- `Pages/Progress/*`
- `Components/ui/*`
- `Components/session/*`

---

## MVP Milestones
1. Exercise CRUD
2. Training builder
3. Start session from training
4. Live logging (sets + rest)
5. Session history
6. Progress tracking

This plan prioritizes **fast delivery**, **clean domain boundaries**, and **long-term extensibility** without premature overengineering.

