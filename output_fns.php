<?php

function display_account_setup($auth_user) {
    display_account_form($auth_user);
    $list = get_account($auth_user);
    $accounts = sizeof($list);

    foreach ($list as $key=>$account) {
        display_account_form($auth_user, $account['accountid'],
            $account['server'], $account['remoteuser'], $account['remotepassword'],
            $account['type'], $account['port']);
    }
}

function do_html_header(){
    if (number_of_accounts($auth_user) > 1) {
        echo "<form action=\"index.php?action=open-mailbox\" method=\"post\">
            <td bgcolor=\"#ff6600\" align=\"right\" valign=\"middle\">";
        display_account_select($auth_user, $selected_account);
        echo "</td>
            </form>";
    }
}

function display_account_select($auth_user, $selected_account){
?>
    <select
        onchange="window.location=this.options[selectedIndex].valuename=account">
        <option
            value="index.php?action=select-account&account=4" selected>
            thickbook.com
        </option>
        <option
            value="index.php?action=select-account&account=3">
            localhost
        </option>
    </select>
<?php
}

function display_list($auth_user, $accountid) {
    global $table_width;
    if (!$accountid) {
        echo "<p style=\"padding-bottom: 100px\">Mailbox is not selected.</p>";
    } else {
        $imap = open_mailbox($auth_user, $accountid);

        if ($imap) {
            echo "<table width=\"".$table_width."\" cellspacing=\"0\"
                cellpadding=\"6\" border=\"0\">";
            $headers = imap_headers($imap);

            $messages = sizeof($headers);
            for ($i = 0; $i < $messages; $i++) {
                echo "<tr><td bgcolor=\"";
                if ($i%2) {
                    echo '#ffffff';
                } else {
                    echo '#ffffcc';
                }
                echo "\"><a href=\"index.php?action=view-message&messageid="
                    .($i+1)."\">";
                echo $headers[$i];
                echo "</a></td></tr>\n";
            }
            echo "</table>";
        } else {
            $account = get_account_settings($auth_user, $accountid);
            echo "<p style=\"padding-bottom: 100px\">Can\'t open mailbox"
                .$account['server'].".</p>";
        }
    }
}

?>