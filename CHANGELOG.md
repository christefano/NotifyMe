# Changelog

## v1.2.1

Adding screenshots. No new functionality or bug fixes.

## v1.2.0

- Added a "Notifications menu entries kept per user" setting (100, 500, 1000, or Unlimited) in *Settings -> Application settings -> NotifyMe*. It defaults to 1000, so installing NotifyMe fixes a Notifications menu that won't open. Each new notification deletes a user's oldest entries beyond the limit, and saving the setting trims everyone's entries at once
- Added vacation mode, which pauses a user's email notifications until their next login. Users turn it on from their profile, and project managers turn it on for a member in one project. Turning it on or off emails the user (and the manager who set it), and any login turns it off. The Notifications menu still records everything
- Added the `notifyme:email:subject` hook, which passes `subject`, `task_id`, `project_name`, `task_title`, `event_name`, and `action`, so TagAlong can apply its email subject format and add its reply token
- Added the `notifyme:email:reply_to` hook, so TagAlong can point replies to the reply-by-email mailbox
- Added `config.php` with `product_name` and `product_url` for the email footer, and an optional `NotifyMe.config.json` in Kanboard's `data/` directory that survives plugin updates. With NotKanboard installed, the footer uses its `footer_name` and `product_url`
- Failed login emails go out at most once per account per hour, and the limit is recorded only after the email sends
- Failed login subjects start with the product name and not `[NotifyMe]`
- "Task updated" emails list what changed
- Comment emails render Markdown
- A task link between two projects notifies the actor when they belong to only one of them
- An email to a user with no full name is addressed to their username
- File emails use the action as the heading, without a repeated "File"
- Fixed the email footer never rendering, which cut off every notification email on PHP 8
- Fixed wiki email links
- Fixed raw keys like `task_created` showing in languages with no NotifyMe translation. They now show English
- A bad `config.php` value no longer throws a `TypeError` in every email
- The project vacation mode page labels its last column "Action", and each Turn on / Turn off button names its member for screen readers

## v1.1.0

- Added optional support for the Wiki plugin: wiki page created / updated / deleted emails
- Added Notifications menu entries alongside email for task, subtask, comment, file, and task link events. Project file and failed login events are email-only
- Added task link created / updated / deleted, project file attached / deleted, and failed login notifications
- Removed project created / removed, category added / removed, and time tracking added / removed, since Kanboard has no events for them
- Fixed v1.0.1 sending no notifications at all
- Fixed notifications going to the task's creator or owner and not to the user who performed the action
- Fixed translations never loading, which showed raw keys like `task_created`
- Fixed Notifications menu entries showing "#0" and linking to a nonexistent task
- Fixed comment bodies and due dates never rendering in task emails
- Fixed "R&D" showing as "R&amp;D" in email subjects
- Fixed task link created / updated sending two notifications
- Fixed the assignee change label always reading "Task assigned to you"

## v1.0.1

- Fixed XSS in all email templates
- Fixed template path injection in the notification renderer
- Fixed duplicate emails when the creator, owner, and actor overlap
- Added a project membership check before notifying users
- Reduced database queries

## v1.0.0

- Initial release
- Email and Notifications menu notifications for 21 user actions
- No database changes
