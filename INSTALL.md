# Installation

## Requirements

- Kanboard >= 1.2.20. No schema changes. User metadata holds the w login limit and the vacation flags.
- Working outgoing email in Kanboard's settings (since most notifications are emails).


## Install

1. Extract `NotifyMe.zip` to `/path/to/kanboard/plugins/NotifyMe/`.
2. Restart PHP-FPM (or your webserver) if Kanboard's plugin cache doesn't pick up the new files.
3. Add a comment on a task in a project you belong to and check that the email arrives.

To change the footer name, install [NotKanboard](https://github.com/christefano/NotKanboard), edit `plugins/NotifyMe/config.php` (or optionally add `NotifyMe.config.json` to Kanboard's `data/` directory so your custom name survives NotifyMe plugin updates). The README's Email footers section lists all the keys.


## Remove

Delete `plugins/NotifyMe`. Vacation-mode flags stay in user metadata and the `notifyme_max_unread` setting stays in the settings table in case NotifyMe is installed again. Kanboard's own `userNotificationModel` resumes, so the Notifications menu stops being trimmed, too.


## Compatibility

- No Kanboard core or third-party plugin files are edited and no templates are overridden, so NotifyMe doesn't conflict with core notification templates.
- Adds the `notifyme:email:subject` and `notifyme:email:reply_to` hooks, which [TagAlong](https://github.com/christefano/TagAlong) uses.
- Replaces Kanboard's `userNotificationModel` for vacation mode and the Notifications menu limit. Another plugin that replaces it, too, will conflict with it, and whichever registers last gets priority.
