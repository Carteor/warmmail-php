<?php

function get_accounts($auth_user)
{
    $list = array();
    if ($conn = db_connect()) {
        $query = "SELECT * FROM accounts WHERE username = '" . $auth_user . "'";
        $result = $conn->query($query);
        if ($result) {
            while ($settings = $result->fetch_assoc()) {
                array_push($list, $settings);
            }
        } else {
            return false;
        }
    }
    return $list;
}

function get_account_list($auth_user)
{
//    echo "Debug: get_account_list()<br />";
    $query = "SELECT remoteuser
              FROM accounts WHERE
              username = '" . $auth_user . "'";
    if ($conn = db_connect()) {
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_array();
            return $row;
        }
    }
    return 0;
}

function store_account_settings($auth_user, $settings)
{
    echo "store_account_settings()<br />";
    if (!filled_out($settings)) {
        echo "<p>All fields must be filled, Try again.</p>";
        return false;
    } else {
        if ($settings['account'] > 0) {
            $query = "UPDATE accounts SET server = '" . $settings['server'] . "',
                port = '" . $settings['port'] . "', type='" . $settings['type'] . "',
                remoteuser = '" . $settings['remoteuser'] . "',
                remotepassword = '" . $settings['remotepassword'] . "'
                WHERE accountid = '" . $settings['account'] . "'
                AND username='" . $auth_user . "'";
        } else {
            $query = "INSERT INTO accounts VALUES (
                '" . $auth_user . "',
                '" . $settings['server'] . "', '" . $settings['port'] . "',
                '" . $settings['type'] . "', '" . $settings['remoteuser'] . "',
                '" . $settings['remotepassword'] . "',
                NULL)";
        }
        if ($conn = db_connect()) {
            $result = $conn->query($query);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "<p>Can\'t save changes.</p>";
            return false;
        }
    }
}

function get_account_settings($auth_user, $accountid) {
//    echo "Debug: get_account_settings()<br />";
//    echo "Debug: accountid: ".$accountid."<br />";
    $conn = db_connect();
    $query = "SELECT * FROM accounts
                WHERE remoteuser = '".$accountid."'
                AND username = '".$auth_user."'";
//    echo "Debug: query: ".$query."<br />";
    $result = $conn->query($query);
//    echo "Debug: num_rows: ",$result->num_rows."<br />";
//    echo "Debug: getType(result): ".gettype($result)."<br />";
//    echo "Debug: item: ";
    $item = $result->fetch_array();
//    echo "Debug: getType(item): ".gettype($item)."<br />";
//    echo $item['type']."<br />";

    if ($result) {
        return $item;
    }
    return false;
}

function delete_account($auth_user, $accountid)
{
    $query = "DELETE FROM accounts
        WHERE accountid = '" . $accountid . "'
        AND username='" . $auth_user . "'";
    if ($conn = db_connect()) {
        $result = $conn->query($query);
        return $result;
    } else {
        return false;
    }
}

function number_of_accounts($auth_user)
{
    $query = "SELECT count(*)
              FROM accounts WHERE
              username = '" . $auth_user . "'";
    if ($conn = db_connect()) {
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_array();
            return $row[0];
        }
    }
    return 0;
}

function open_mailbox($auth_user, $accountid)
{
//    echo "Debug: open_mailbox()<br />";
    if (number_of_accounts($auth_user) == 1) {
        $accounts = get_account_list($auth_user);
        $_SESSION['selected_account'] = $accounts[0];
        $accountid = $accounts[0];
    }

    $settings = get_account_settings($auth_user, $accountid);
//    echo "Debug: settings[server]: ".$settings['server']."<br />";
    if (!sizeof($settings)) {
        return 0;
    }
    $mailbox = '{' . $settings['server'];
    if ($settings['type'] == 'POP3') {
        $mailbox .= '/pop3';
    }
    $mailbox .= ':' . $settings['port'] . '/ssl}INBOX';
//    echo "mailbox: ".$mailbox."<br />";
    $imap = imap_open($mailbox, $settings['remoteuser'], $settings['remotepassword']);

    return $imap;
}

function retrieve_message($auth_user, $accountid, $messageid, $fullheaders)
{
    $message = array();
    if (!($auth_user && $messageid && $accountid)) {
        return false;
    }

    $imap = open_mailbox($auth_user, $accountid);
    if (!$imap) {
        return false;
    }

    $header = imap_header($imap, $messageid);
    if (!$header) {
        return false;
    }

    $message['body'] = imap_body($imap, $messageid);
    if (!$message['body']) {
        $message['body'] = "[There is no body of the message]\n\n\n\n\n\n";
    }
    if ($fullheaders) {
        $message['fullheaders'] = '';
    }

    $message['subject'] = $header->subject;
    $message['fromaddress'] = $header->fromaddress;
    $message['toaddress'] = $header->toaddress;
    $message['ccaddress'] = $header->ccaddress;
    $message['date'] = $header->date;

    imap_close($imap);
    return $message;
}

function delete_message($auth_user, $accountid, $messageid)
{
    $imap = open_mailbox($auth_user, $accountid);
    if ($imap) {
        imap_delete($imap, $messageid);
        imap_expunge($imap);
        imap_close($imap);
        return true;
    }
    return false;
}

function send_message($to, $cc, $subject, $message)
{
    if (!$conn = db_connect()) {
        return false;
    }

    $query = "SELECT address FROM users WHERE
                username ='" . $_SESSION['auth_user'] . "'";

    $result = $conn->query($query);
    if (!$result) {
        return false;
    } else if ($result->num_rows == 0) {
        return false;
    } else {
        $row = $result->fetch_object();
        $other = 'From: ' . $row->address;
        if (!empty($cc)) {
            $other .= "\r\nCc: $cc";
        }
        if (mail($to, $subject, $message, $other)) {
            return true;
        } else {
            return false;
        }
    }
}

function add_quoting($hz) {
    return $hz;
}