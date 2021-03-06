<?php
require_once("pages.php");
require_once("pwcheck.php");
$act     = "users";
$ref     = pageLinkAct();
$pwcheck = "";
pageHeader();

if (isset($_POST['create']) && !empty($_POST['newUser']) && isset($_POST['newRole'])) {
  // create user
  $user  = $_POST['newUser'];
  $pass  = (!empty($_POST['newPass']) ? $_POST['newPass'] : false);
  $admin = ($_POST['newRole'] == 1);

  if (!pwCheck($pass)) {
    $pwcheck = T_("The password you typed does not meet the password policy requirements.");
    errorMessage(T_("Creation failed"), sprintf(T_("user \"%s\"") . "<br>%s", htmlEntUTF8($user), $pwcheck));
  } else {
    if (userAdd($user, $pass, $admin)) {
      infoMessage(T_("Created"), htmlEntUTF8($user));
    } else {
      errorMessage(T_("Creation failed"), sprintf(T_("user \"%s\" already exists"), htmlEntUTF8($user)));
    }
  }
}

if (isset($_POST["delete"]) && !empty($_POST["sel"])) {
  $list = array();

  // delete users
  foreach ($_POST["sel"] as $name) {
    if (userDel($name)) {
      $list[] = htmlEntUTF8($name);
    }
  }

  if (count($list)) {
    infoMessage(T_("Deleted"), $list);
  }
}

if (isset($_POST['apply']) && !empty($_POST['user']) && is_array($_POST['user']) && !empty($_POST['role']) && is_array($_POST['role']) && !empty($_POST['pass']) && is_array($_POST['pass']) && count($_POST['user']) == count($_POST['role']) && count($_POST['role']) == count($_POST['pass'])) {
  $user = $_POST['user'];
  $role = $_POST['role'];
  $pass = $_POST['pass'];
  $list = array();

  for ($i = 0; $i != count($user); ++$i) {
    $o = userAdm($user[$i]);
    if (is_null($o)) {
      continue;
    }

    $role[$i] = ($role[$i] == 1);
    $sameRole = ($o == $role[$i]);
    $samePass = empty($pass[$i]);
    if ($sameRole && $samePass) {
      continue;
    }

    # Insert by Thomas
    if (!pwCheck($pass[$i])) {
      $list[]  = htmlEntUTF8($user[$i]);
      $pwcheck = T_("The password you typed does not meet the password policy requirements");
    } else {
      if (userUpd($user[$i], ($samePass ? null : $pass[$i]), ($sameRole ? null : $role[$i]))) {
        $list[] = htmlEntUTF8($user[$i]);
      }
    }
  }

  # Insert by Thomas
  if ($pwcheck) {
    errorMessage($pwcheck, $list);
  } elseif (count($list)) {
    errorMessage(T_("Updated"), $list);
  }
}

function htmlRole($name, $selected) {
  // role
  $ret = "<select class=\"element select\" name=\"$name\">";
  foreach (array(T_("Administrator") => 1, T_("User") => 0) as $role => $admin) {
    $ret .= "<option value=\"$admin\"";
    if ($selected == $admin) {
      $ret .= " selected=\"selected\"";
    }
    $ret .= ">$role</option>";
  }
  $ret .= "</select>";
  return $ret;
}

// list users
$sql = <<<EOF
  SELECT u.name, admin, t.count as tickets, g.count as grants, t.size
  FROM "user" u
  LEFT JOIN role r ON r.id = u.role_id
  LEFT JOIN (
      SELECT u.id AS id, count(t.id) as count, sum(t.size) as size
      FROM "user" u
      LEFT JOIN ticket t ON t.user_id = u.id
      GROUP BY u.id
    ) t ON t.id = u.id
  LEFT JOIN (
      SELECT u.id AS id, count(g.id) as count
      FROM "user" u
      LEFT JOIN "grant" g ON g.user_id = u.id
      GROUP BY u.id
    ) g ON g.id = u.id
  ORDER BY u.name
EOF;
?>
<form action="<?php echo $ref; ?>" method="post">
  <table class="sortable" id="users">
    <thead>
      <tr>
        <th><input class="element checkbox" type="checkbox" onclick="selectAll(this.checked);"/></th>
        <th data-sort="string" class="sorting-asc"><?php echo T_("User"); ?></th>
        <th><?php echo T_("Password"); ?></th>
        <th data-sort="int"><?php echo T_("Role"); ?></th>
        <th data-sort="int"><?php echo T_("Tickets"); ?></th>
        <th data-sort="int"><?php echo T_("Grants"); ?></th>
        <th data-sort="int"><?php echo T_("Total size"); ?></th>
      </tr>
    </thead>
    <tbody>
        <?php
        foreach ($db->query($sql) as $DATA) {
          // selection
          echo "<tr><td><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\""
          . htmlEntUTF8($DATA['name']) . "\"/></td>";

          // name/password
          echo "<td>" . htmlEntUTF8($DATA['name']) . "</td>";
          echo "<td><input type=\"hidden\" name=\"user[]\" value=\""
          . htmlEntUTF8($DATA['name']) . "\"/><input class=\"element text\""
          . " type=\"text\" name=\"pass[]\"></td>";

          // role
          echo '<td data-sort-value="' . $DATA['admin'] . '">'
          . htmlRole("role[]", $DATA['admin']) . '</td>';

          // tickets/grants
          echo "<td>$DATA[tickets]</td><td>$DATA[grants]</td>";

          // total size
          echo '<td data-sort-value="' . (int) $DATA['size'] . '">'
          . humanSize($DATA['size']) . '</td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
      <tr>
        <td></td>
        <td><input class="element text" type="text" name="newUser"></td>
        <td><input class="element text password" type="text" name="newPass"></td>
        <td><?php echo htmlRole("newRole", 0); ?></td>
        <td colspan="3">
          <input class="element button" type="submit" name="create" value="<?php echo T_("Create"); ?>"/>
        </td>
      </tr>
    </tfoot>
  </table>
  <ul>
    <li class="buttons">
      <input type="submit" name="reload" value="<?php echo T_("Reload"); ?>"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="submit" name="delete" value="<?php echo T_("Delete selected"); ?>"/>
      <input type="submit" name="apply" value="<?php echo T_("Apply changes"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
