## ADDED Requirements

### Requirement: Volunteer Application Submission
The system SHALL accept POST requests at `/volunteer-apply.php` containing `job_id`, `name`, `phone`, `email`, and `message`, and persist valid applications into the `volunteer_applications` table.

#### Scenario: Successful application submission
- **WHEN** a user fills out the volunteer form on `/volunteer.php` with a valid job ID, name, and phone number, and submits
- **THEN** the system saves the application with status '待審核', sets a success flash message in session, and redirects to `/volunteer.php` displaying a success notification

#### Scenario: Missing required fields
- **WHEN** a user submits the volunteer form missing the name or phone number
- **THEN** the system sets an error flash message in session and redirects back to `/volunteer.php` without inserting any record

#### Scenario: Submission for inactive or non-existent job
- **WHEN** a user submits an application for a `job_id` that is inactive or does not exist
- **THEN** the system rejects the submission gracefully and alerts the user that the job is no longer accepting applications
