<?php

function get_accounts($auth_user) {
    $list = array();
    if ($conn->db_connect()) {
        $query = "select * from accounts where username = '".$auth_user."'";
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

function store_account_settings($auth_user, $settings) {
    if (!filled_out($settings)) {
        echo "<p>All fields must be filled, Try again.</p>";
        return false;
    } else {
        if ($settings['account'] > 0) {
            $query = "update accounts set server = '".$settings[server]."',
                port = '".$settings[port]."', type='".$settings[type]."',
                remoteuser = '".$settings[remoteuser]."',
                remotepassword = '".$settings[remotepassword]."'
                where accountid = '".$settings[account]."'
                and username='".$auth_user."'";
        } else {
            $query = "insert into accounts values ('".$auth_user."',
            '".$settings[server]."', '".$settings[port]."',
            '".$settings[type]."', '".$settings[remoteuser]."',
            '".$settings[remotepassword]."', NULL)";
        }
        if ($conn->db_connect()) {
            $result = $conn->query($query);
            if ($query) {
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

function delete_account($auth_user, $accountid) {
    $query = "delete from accounts where accountid = '".$accountid."'
        and username='".$auth_user."'";
    if (db_connect()) {
        $result = $conn->query($query);
    }
    return $result;
}

function number_of_accounts($auth_user) {
    $query = "select count(*) from accounts where
      username = '".$auth_user."'";
    if (db_connect()) {
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_array();
            return $row[0];
        }
    }
    return 0;
}

function open_mailbox($auth_user, $accountid){
    if (number_of_accounts($auth_user) == 1) {
        $accounts = get_account_list($auth_user);
        $_SESSION['selected_account'] = $accounts[0];
        $accountid = $accounts[0];
    }

    $settings = get_account_settings($auth_user, $accountid);
    if (!sizeof($settings)) {
        return 0;
    }
    $mailbox = '{'.$settings[server];
    if ($settings[type] == 'POP3') {
        $mailbox .= '/pop3';
    }
    $mailbox .= ':'.$settings[port].'}INBOX';
    @$imap = imap_open($mailbox, $settings['remoteuser'], $settings['remotepassword']);
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

function delete_message($auth_user, $accountid, $messageid) {
    $imap = open_mailbox($auth_user, $accountid);
    if ($imap) {
        imap_delete($imap, $messageid);
        imap_expunge($imap);
        imap_close($imap);
        return true;
    }
    return false;
}

function send_message($to, $cc, $subject, $message) {
    if (!$conn->db_connect()) {
        return false;
    }

    $query = "select address from users where
              username='".$_SESSION['auth_user']."'";

    $result = $conn->query($query);
    if (!$result) {
        return false;
    } else if ($result->num_rows == 0) {
        return false;
    } else {
        $row = $result->fetch_object();
        $other = 'From: '.$row->address;
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

?>