<?php

include('include_fns.php');
session_start();

@ $username = $_POST['username'];
@ $passwd = $_POST['passwd'];
@ $action = $_POST['action'];
@ $account = $_POST['account'];
@ $messageid = $_POST['messageid'];

@ $to = $_POST['username'];
@ $passwd = $_POST['passwd'];
@ $action = $_REQUEST['action'];
@ $account = $_REQUEST['account'];
@ $messageid = $_GET['messageid'];

@ $to = $_POST['to'];
@ $cc = $_POST['cc'];
@ $subject = $_POST['subject'];
@ $message = $_POST['message'];

$buttons = array();

$status = '';
if ($username || $passwd) {
    if (login($username, $passwd)) {

        $status .=
            "<p style=\"padding-bottom: 100px\">
            You successfully log in in system.</p>";
        $_SESSION['auth_user'] = $username;
//        echo "Debug: number_of_accounts_SESSION[auth_user]): ".number_of_accounts($_SESSION['auth_user'])."<br />";
        if (number_of_accounts($_SESSION['auth_user']) == 1) {
//            echo "Debug: if (number_of_accounts(_SESSION[auth_user]) == 1)<br />";
            $accounts = get_account_list($_SESSION['auth_user']);
//            echo "Debug: after get_account_list(): ".$accounts[0]."<br />";

            $_SESSION['selected_account'] = $accounts[0];
//            echo "Debug: session: ".$_SESSION['selected_account']."<br />".
//                "accounts[0]: ".$accounts[0]."<br />";
        }
    } else {
        $status .= "<p style=\"padding-bottom: 100px\">
            Sorry, but you cannot log in with this username and password.</p>";
    }
}

if ($action == 'log-out') {
    session_destroy();
    unset($action);
    $_SESSION = array();
}

switch (@ $action) {
    case 'delete-account':
        delete_account($_SESSION['auth_user'], $_POST);
        break;
    case 'store-settings':
        store_account_settings($_SESSION['auth_user'], $_POST);
        break;
    case 'select-account':
        if (($account) && (account_exists($_SESSION['auth_user'], $account))) {
            $_SESSION['selected_account'] = $account;
        }
        break;
}

$buttons[0] = 'view-mailbox';
$buttons[1] = 'new-message';
$buttons[2] = 'account-setup';

if (check_auth_user()) {
    $buttons[4] = 'log-out';
}
//2
if ( @ $action) {
    @ do_html_header($_SESSION['auth_user'],
        "Warm mail - " . format_action($action),
        $_SESSION['selected_account']);
} else {
    @ do_html_header($_SESSION['auth_user'], "Warm mail",
        $_SESSION['selected_account']);
}
display_toolbar($buttons);

//3
echo $status;

if (!check_auth_user()) {
    echo "You must log in";
    if (@( $action) && ($action != 'log-out')) {
        echo " and then go to " . format_action($action);
    }
    echo ".</p>";
    display_login_form(@ $action);
} else {
    switch ($action) {
        case 'store-settings':
        case 'account-setup':
        case 'delete-account':
            display_account_setup($_SESSION['auth_user']);
            break;
        case 'send-message':
            if (send_message($to, $cc, $subject, $message)) {
                echo "<p style=\"padding-bottom: 100px\">Message sent.</p>";
            } else {
                echo "<p style=\"padding-bottom: 100px\">
                    Cannot send message.</p>";
            }
            break;
        case 'delete':
            delete_message($_SESSION['auth_user'],
                $_SESSION['selected_account'], $messageid);
        case 'select-account':
        case 'view-mailbox':
            display_list($_SESSION['auth_user'], $_SESSION['selected_account']);
            break;
        case 'show-headers':
        case 'hide-headers':
        case 'view-message':
            $fullheaders = ($action == 'show-headers');
            display_message($_SESSION['auth_user'],
                $_SESSION['selected_account'],
                $messageid, $fullheaders);
            break;
        case 'reply-all': {
            if (!$imap) {
                $imap = open_mailbox($_SESSION['auth_user'],
                    $_SESSION['selected_account']);
            }
            if ($imap) {
                $header = imap_header($imap, $messageid);
                if ($header->reply_toaddress) {
                    $to = $header->reply_toaddress;
                } else {
                    $to = $header->fromaddress;
                }
                $cc = $header->ccaddress;
                $subject = "Re: " . $header->subject;
                $body = add_quoting(stripslashes(imap_body($imap, $messageid)));
                imap_close($imap);
                display_new_message_form($_SESSION['auth_user'],
                    $to, $cc, $subject, $body);
            }
        }
            break;
        case 'reply':
            if (!$imap) {
                $imap = open_mailbox($_SESSION['auth_user'],
                    $_SESSION['selected_account']);
            }

            if ($imap) {
                $header = imap_header($imap, $messageid);
                if ($header->reply_toaddress) {
                    $to = $header->fromaddress;
                }

                $subject = "Re: " . $header->subject;
                $body = add_quoting(stripslashes(imap_body($imap, $messageid)));
                imap_close($imap);

                display_new_message_form($_SESSION['auth_user'],
                    $to, $cc, $subject, $body);
            }
            break;

        case 'forward':
//            echo "Debug: forward: <br />";
            if (!$imap) {
                $header = imap_header($imap, $messageid);
                $body = stripslashes(imap_body($imap, $messageid));
//                echo "Debug: body: ".$body."<br />";
                $subject = "Fwd: " . $header->subject;
                imap_close($imap);
                display_new_message_form($_SESSION['auth_user'],
                    $to, $cc, $subject, $body);
            }
            break;

        case 'new-message':
            $body ='';
            display_new_message_form($_SESSION['auth_user'],
                $to, $cc, $subject, $body);
            break;
    }
}

//4
do_html_footer();
?>