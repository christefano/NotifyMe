# Installation

## Requirements

- Kanboard >= 1.2.20. No schema changes. User metadata holds the failed login limit (`notifyme_auth_failure_at`) and the vacation flags (`notifyme_vacation`, `notifyme_vacation_project_<id>`, and `notifyme_vacation_setter_<id>`).
- Working outgoing email in Kanboard's settings (since most notifications are emails).


## Install

1. Extract the release zip (`NotifyMe-v1.2.1.zip`) to `/path/to/kanboard/plugins/NotifyMe/`.
2. Restart PHP-FPM (or your webserver) if Kanboard's plugin cache doesn't pick up the new files.
3. Add a comment on a task in a project you belong to and check that the email arrives.

To change the footer name, install [NotKanboard](https://github.com/christefano/NotKanboard) or edit `plugins/NotifyMe/config.php`. To keep your custom name through a NotifyMe update, copy [`NotifyMe.config.json`](NotifyMe.config.json) to Kanboard's `data/` directory instead. The README's Email footers section lists all the keys.


## Remove

Delete `plugins/NotifyMe`. Vacation mode flags stay in user metadata and the `notifyme_max_unread` setting stays in the settings table in case NotifyMe is installed again. Kanboard's own `userNotificationModel` resumes, so the Notifications menu stops being trimmed, too.


## Compatibility

- No Kanboard core or third-party plugin files are edited and no templates are overridden, so NotifyMe doesn't conflict with core notification templates.
- Adds the `notifyme:email:subject` and `notifyme:email:reply_to` hooks, which [TagAlong](https://github.com/christefano/TagAlong) uses.
- Replaces Kanboard's `userNotificationModel` for vacation mode and the Notifications menu limit. A plugin that replaces it too conflicts, and whichever registers last gets priority.
