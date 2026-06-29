# Server and Deployment Rules

## Queue Worker Requirement
Whenever starting the local server (e.g., using `php artisan serve`), you MUST also start the Laravel queue worker (`php artisan queue:work`) either via concurrently or in a separate terminal.
When deploying the website to a production/staging environment, you MUST ensure that a process monitor (like Supervisor) is configured to keep the `php artisan queue:work` process running in the background.
