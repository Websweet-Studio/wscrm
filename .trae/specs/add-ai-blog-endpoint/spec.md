# AI Agent Blog Endpoint Spec

## Why
Agen AI (seperti Hermes Agent) perlu bisa membuat blog post secara programatik tanpa melalui UI admin. Endpoint API ini memungkinkan insert blog post via API key.

## What Changes
- **NEW** `App\Http\Controllers\Api\AgentBlogController` — endpoint JSON untuk insert blog post
- **NEW** Route `POST /api/agent/blog` di routes/web.php
- **NEW** Middleware `App\Http\Middleware\AgentTokenAuth` — validasi Bearer token
- **NEW** Config key `services.agent.token` — token dari `.env`
- No perubahan ke model/controller/migration existing

## Impact
- Affected specs: Blog, API
- Affected code: `routes/web.php`, `config/services.php`, new controller & middleware

## ADDED Requirements

### Requirement: AI Agent Blog Post Creation
The system SHALL provide a JSON API endpoint for AI agents to create blog posts.

#### Scenario: Create blog post with valid token
- **GIVEN** Agent has valid Bearer token
- **WHEN** POST request sent to `/api/agent/blog` with valid blog data
- **THEN** response 201 with `{ "data": { "id": ..., "title": ..., "slug": ... } }`

#### Scenario: Create blog post without token
- **WHEN** POST request sent without Authorization header
- **THEN** response 401 `{ "message": "Unauthenticated." }`

#### Scenario: Create blog post with invalid token
- **WHEN** POST request sent with wrong Bearer token
- **THEN** response 401 `{ "message": "Invalid agent token." }`

#### Scenario: Validation failure
- **WHEN** POST request sent with missing required fields
- **THEN** response 422 with validation errors

## REMOVED Requirements
None.
