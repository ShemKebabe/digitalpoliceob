<?php
// Who is signed in? Used by nav.js to fill the sidebar and show role-specific menu items.
require 'auth.php';

$o = require_role();
respond(['name' => $o['full_name'], 'rank' => $o['rank'], 'ap' => $o['ap_number'], 'role' => $o['role']]);