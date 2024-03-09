<?php

function convertSlug($slug) {
    if (!(isset($slug) && $slug !== '')) return $slug;
    $slug = preg_replace('~[^\pL\d]+~u', "-", $slug);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, "-");
    $slug = preg_replace('~-+~', "-", $slug);
    $slug = strtolower($slug);
    return $slug;
}

function slugGenerator($args) {
    global $categoryName, $title, $username;
    global $q1, $q2, $q3, $q4;
    if (isset($categoryName)) $slug = $categoryName;
    else if (isset($username)) $slug = $username;
    else if (isset($title)) $slug = $title;
    
    $slug = convertSlug($slug);
    
    $url_parsed = parse_url("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    
    if ($q1 == "category" || $q1 == "thread") {
        if (is_numeric($q3)) $newURL = genURL($q1 . "/" . $q2 . "/" . $q3 . "/" . $slug);
        else if (is_numeric($slug)) $newURL = genURL($q1 . "/" . $q2 . "/" . 1 . "/" . $slug);
        else $newURL = genURL($q1 . "/" . $q2 . "/" . $slug);
        if (isset($url_parsed["query"])) $newURL .= "?" . $url_parsed["query"];
    }
    if ($q1 == "user") {
        $newURL = genURL($q1 . "/" . $q2 . "/" . $slug);
        if (isset($url_parsed["query"])) $newURL .= "?" . $url_parsed["query"];
    }
    
    if (isset($newURL)) {
        echo "<script type='text/javascript'>";
        echo "window.history.replaceState(null, '', '" . $newURL . "' + window.location.hash.substr(0))"; 
        echo "</script>\n";
    }
}

function slugGenURL($args) {
    global $db;
    $url = $args[1];
    $url_parts = explode('/', parse_url($url, PHP_URL_PATH));
    
    if ($url_parts[0] == "user") {
        if (!isset($url_parts[2])) {
            $result = $db->query("SELECT username FROM users WHERE userid='" . $db->real_escape_string($url_parts[1]) . "'");
            $slug = $result->fetch_row()[0];
            
            $args[0] .= "/" . convertSlug($slug);
        }
    }
    
    if ($url_parts[0] == "thread") {
        if (!isset($url_parts[3]) && is_numeric($url_parts[2]) || !isset($url_parts[2])) {
            $result = $db->query("SELECT title FROM threads WHERE threadid='" . $db->real_escape_string($url_parts[1]) . "'");
            $slug = $result->fetch_row()[0];
            
            if (is_numeric(convertSlug($slug)) && !isset($url_parts[2])) $args[0] .= "/" . 1;
            $args[0] .= "/" . convertSlug($slug);
        }
    }
    
    if ($url_parts[0] == "category") {
        if (!isset($url_parts[3]) && is_numeric($url_parts[2]) || !isset($url_parts[2])) {
            $result = $db->query("SELECT categoryname FROM categories WHERE categoryid='" . $db->real_escape_string($url_parts[1]) . "'");
            $slug = $result->fetch_row()[0];
            
            if (is_numeric(convertSlug($slug)) && !isset($url_parts[2])) $args[0] .= "/" . 1;
            $args[0] .= "/" . convertSlug($slug);
        }
    }
    return;
}

if ($config["modRewriteDisabled"] != 1) { // If we're not using mod rewrite, do nothing.
    hook("meta", "slugGenerator");
    hook("beforeReturnGeneratedURL", "slugGenURL");
}
?>
