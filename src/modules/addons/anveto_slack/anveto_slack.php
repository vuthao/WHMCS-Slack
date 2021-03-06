<?php
/**
 * Copyright Anveto AB
 * Author: Markus Tenghamn
 * Date: 26/12/15
 * Time: 12:38
 * This is not to be removed.
 *
 * WHMCS Slack
 *
 * Addon was created by Anveto to post updates to slack automatically
 *
 * @author     Anveto <dev@anveto.com>
 * @copyright  Copyright (c) Anveto 2016
 * @version    $Id$
 * @link       http://anveto.com
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once dirname(__FILE__) . '/db.php';

function anveto_slack_getmodulename()
{
    return "WHMCS Slack";
}

function anveto_slack_config()
{
    $configarray = array(
        "name" => anveto_slack_getmodulename(),
        "description" => "This plugin will post to slack when events happen in your WHMCS installation. Remember to configure the plugin.",
        "version" => "1.0",
        "author" => "Anveto",
        "language" => "english",
        "fields" => array(
            "token" => array("FriendlyName" => "Token", "Type" => "text", "Size" => "25", "Description" => "Get the token from your Slack integrations page.", "Default" => "Slack token",),
            "botname" => array("FriendlyName" => "Post as", "Type" => "text", "Size" => "25", "Description" => "Usually the name of a bot", "Default" => "WHMCS bot",),
        ));
    return $configarray;
}

function anveto_slack_getbaseurl()
{
    $base = __DIR__;
    $base = str_replace("modules/addons/anveto_slack", "", $base);
    return $base;
}

function anveto_slack_activate()
{
    if (function_exists("full_query")) {
        $val = full_query('SELECT 1 FROM mod_anveto_slack_hooks');
    }

    if($val !== FALSE)
    {
        return array(
            'status' => 'success',
            'description' => 'Anveto Slack has been activated.'
        );
    }

    try {
        full_query("CREATE TABLE mod_anveto_slack_hooks (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, hook VARCHAR(200) NOT NULL, channel VARCHAR(200) NOT NULL, text VARCHAR(200), created_at TIMESTAMP)");
        return array('status' => 'success', 'description' => 'Anveto Slack has been activated.');
    } catch (ErrorException $e) {
        return array('status' => 'error', 'description' => 'Could not activate Anveto Slack.');
    }


}

function anveto_slack_deactivate()
{
    return array('status' => 'success', 'description' => 'Thanks for using ' . anveto_slack_getmodulename());
}

function anveto_slack_upgrade($vars)
{

    $version = $vars['version'];

}

function anveto_slack_output($vars)
{
    global $hooksArray;
    global $_POST;
    $table = "mod_anveto_slack_hooks";

    if (isset($_POST['hook'])) {
        //New hook is being added
        $hook = (string)$_POST['hook'];
        if (isset($hooksArray[$hook])) {
            $values = array("hook" => $_POST['hook'], "channel" => $hooksArray[$hook]['channel'], "text" => $hooksArray[$hook]['default'], "created_at" => date("Y-m-d H:i:s"));
            $newid = insert_query($table, $values);
        } else {
            echo "<b>Error:</b> not a valid hook.";
        }
    } else if (isset($_POST['updateHook'])) {
        $update = array("channel"=>$_POST['channel'], "text"=>$_POST['text']);
        $where = array("id"=>$_POST['updateHook']);
        update_query($table,$update,$where);
    } else if (isset($_POST['deleteHook'])) {
        if (is_numeric($_POST['deleteHook'])) {
            full_query("DELETE FROM ".$table." WHERE id = ".$_POST['deleteHook']);
        }
    }

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $LANG = $vars['_lang'];

    foreach ($hooksArray as $k=>$h) {
        echo $k."<br/>";
    }

    echo '<form method="post" action="">';
    echo '<select name="hook">';
    foreach ($hooksArray as $k=>$h) {
        echo '<option value="'.$k.'">'.$k.'</option>';
    }
    echo '</select>';
    echo '<input type="submit" value="Add Hook">';
    echo '</form>';
    $fields = "id,hook,channel,text";
    $where = array();
    $result = select_query($table,$fields, $where);
    while ($d = mysql_fetch_array($result)) {
        echo '<div>';
        echo '<form method="post" action="">';
        echo '<h3>'.$d['hook'].'</h3><br/>';
        echo '<input type="hidden" name="updateHook" value="'.$d['id'].'">';
        echo '<b><input type="text" name="channel" value="'.$d['channel'].'"></b><br/>';
        echo '<b><textarea cols="50" rows="3" name="text">'.$d['text'].'</textarea></b><br/>';
        echo '<b>Available parameters: </b>'.implode(", ", $hooksArray[$d['hook']]['args']).'<br/>';
        echo '<b>Description: </b>'.$hooksArray[$d['hook']]['description'].'<br/>';
        echo '<input type="submit" value="Update Hook">';
        echo '</form>';

        echo '<form method="post" action="">';
        echo '<input type="hidden" name="deleteHook" value="'.$d['id'].'">';
        echo '<input type="submit" value="Delete Hook">';
        echo '</form>';
        echo '<br/>';
        echo '</div>';
    }



}

function anveto_slack_sidebar($vars)
{

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
    $option2 = $vars['option2'];
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" />' . anveto_slack_getmodulename() . '</span>
    <ul class="menu">
        <li>Version: ' . $version . '</li>
    </ul>';
    return $sidebar;

}