<?php

function display_account_setup($auth_user)
{
//add new account
    display_account_form($auth_user);
    $list = get_accounts($auth_user);
//    $accounts = sizeof($list);
//display all accounts
    foreach ($list as $key => $account) {
        display_account_form($auth_user,
            $account['accountid'],
            $account['server'],
            $account['remoteuser'],
            $account['remotepassword'],
            $account['type'],
            $account['port']);
    }
}

function display_account_form($auth_user, $accountid = '', $server = '',
                              $remoteuser = '', $remotepassword = '', $type = '', $port = '')
{
    ?>
    <form action="index.php?action=store-settings" method="post">
        <table>
            <tr>
                <td>Server name:</td>
                <td><input type="text" name="server" value="<?php echo $server;?>" title="Server name"/></td>
            </tr>
            <tr>
                <td>Port number:</td>
                <td><input type="text" name="port" value="<?php echo $port;?>" title="Port number"/></td>
            </tr>
            <tr>
                <td>Server type:</td>
                <td>
                    <select name="type" title="Server type">
                        <option value="imap">IMAP</option>
                        <option value="pop3">POP3</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="remoteuser" value="<?php echo $remoteuser;?>" title="Username"/></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="remotepassword" value="<?php echo $remotepassword;?>" title="Password"/></td>
            </tr>
        </table>
        <input type="submit" value="Save Changes"/>
    </form>
    <?php
}

function do_html_header($auth_user='', $title, $selected_account='')
{
    ?>
    <html>
    <head>
        <title><?php echo $title; ?></title>
    </head>
    <body>
    <h1><?php echo $title; ?></h1>
    <?php
    if (number_of_accounts($auth_user) > 1) {
        echo "<form action=\"index.php?action=open-mailbox\" method=\"post\">
            <td bgcolor=\"#ff6600\" align=\"right\" valign=\"middle\">";
        display_account_select($auth_user, $selected_account);
        echo "</td>
            </form>";
    }
}

function do_html_footer()
{
    ?>
    </body>
    </html>
    <?php
}

function display_toolbar($buttons)
{
    foreach ($buttons as $item) {
        display_button($item);
    }
}

function display_account_select($auth_user, $selected_account)
{
    echo "display_account_select()<br />";
    ?>
    <select>

    </select>
    <?php
}

function display_list($auth_user, $accountid)
{
    global $table_width;
    echo $accountid;
    if (!$accountid) {
        echo "<p style=\"padding-bottom: 100px\">Mailbox is not selected.</p>";
    } else {
        $imap = open_mailbox($auth_user, $accountid);

        if ($imap) {
            echo "<table width=\"" . $table_width . "\" cellspacing=\"0\"
                cellpadding=\"6\" border=\"0\">";
            $headers = imap_headers($imap);

            $messages = sizeof($headers);
            for ($i = 0; $i < $messages; $i++) {
                echo "<tr><td bgcolor=\"";
                if ($i % 2) {
                    echo '#ffffff';
                } else {
                    echo '#ffffcc';
                }
                echo "\"><a href=\"index.php?action=view-message&messageid="
                    . ($i + 1) . "\">";
                echo $headers[$i];
                echo "</a></td></tr>\n";
            }
            echo "</table>";
        } else {
            $account = get_account_settings($auth_user, $accountid);
            echo "<p style=\"padding-bottom: 100px\">Can\'t open mailbox"
                . $account['server'] . ".</p>";
        }
    }
}

function display_login_form($action)
{
    echo $action;
    ?>
    <h2>Log in</h2>
    <form action="index.php" method="post">
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" title="Username"/></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" title="Password"/></td>
            </tr>
        </table>
        <input type="submit" value="Log In"/>
    </form>
    <?php
}

function display_button($action) {
    echo "<a href=\"index.php?action=".$action."\">".$action."</a><br />";
}

function display_message($auth_user, $selected_account, $messageid,
                         $fullheaders) {
    echo "User: ".$auth_user."<br />".
        "Selected account: ".$selected_account . "<br />" .
        "Messageid: " . $messageid . "<br />" .
        "Full header: " . $fullheaders . "<br />";
}

function display_new_message_form($auth_user, $to, $cc,
                                  $subject, $body) {
?>
<form action="index.php?action=send-message" method="post">
    To address: <input type="text" name="to" title="To/Address"
                    value="<?php echo $to; ?>"/> <br />
    Address/CC: <input type="text" name="cc" title="CC"
                    value="<?php echo $cc; ?>"/> <br />
    Subject: <input type="text" name="subject" title="Subject"
        value="<?php echo $subject; ?>"/> <br />
    <textarea rows="4" cols="50" name="message" title="Message"></textarea> <br />
    <input type="submit" value="Send message" row="3"/> <br />
</form>

<?php
}