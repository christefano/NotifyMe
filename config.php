<?php

// The product name that NotifyMe prints in its email footer:
//
// "This is an automated notification from <product_name>."
//
// Delete or comment out a line to fall back to "Kanboard" with no link.
//
// Ignored when NotKanboard is installed: its footer_name and product_url are used instead.
//
// data/NotifyMe.config.json (if present) overrides this file and survives plugin updates.

return array(

    'product_name' => 'Kanboard',

    // Optional. When set (http or https only), the name links here.
    'product_url' => '',
);
