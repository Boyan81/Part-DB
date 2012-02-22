<?PHP
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

    $Id$

*/
    include ("lib.php");
    partdb_init();

    /*
     * 'action' is a hidden field in the form.
     * 'r' is for remove, 'a' is for add.
     * The 'instock' value has to be changed before the output begins.
     */

    // set action to default, if not exists
    $action = ( isset( $_REQUEST["action"]) ? $_REQUEST["action"] : 'default');

    if ( $action == "r")
    {
        $query = "UPDATE parts SET instock=instock-". smart_escape($_REQUEST["n_less"]) ." WHERE id=". smart_escape($_REQUEST["pid"]) ." AND instock >= ". smart_escape($_REQUEST["n_less"]) ." LIMIT 1;";
        mysql_query ($query);
    }
    
    if ( $action == "a")
    {
        $query = "UPDATE parts SET instock=instock+". smart_escape($_REQUEST["n_more"]) ." WHERE id=". smart_escape($_REQUEST["pid"]) ." LIMIT 1;";
        mysql_query ($query);
    }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
          "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Detailinfo</title>
    <?php print_http_charset(); ?>
    <link rel="StyleSheet" href="css/partdb.css" type="text/css">
    <script type="text/javascript" src="popup.php"></script>
</head>
<body class="body">

<div class="outer">
    <h2>Detailinfo zu &quot;<?PHP print lookup_part_name ($_REQUEST["pid"]); ?>&quot;</h2>
    <div class="inner">
        
        <table>
        <tr valign="top">
        <td>
        <table><?php
        $query = "SELECT ".
            " parts.id,".
            " parts.name,".
            " parts.instock,".
            " parts.mininstock,".
            " footprints.name AS 'footprint',".
            " storeloc.name AS 'location',".
            " storeloc.is_full AS 'location_is_full',".
            " suppliers.name AS 'supplier',".
            " parts.supplierpartnr,".
            " preise.preis,".
            " preise.ma,".
            " parts.comment ".
            " FROM parts ".
            " LEFT JOIN footprints ON parts.id_footprint=footprints.id ".
            " LEFT JOIN storeloc ON parts.id_storeloc=storeloc.id ".
            " LEFT JOIN suppliers ON parts.id_supplier=suppliers.id ".
            " LEFT JOIN preise ON parts.id=preise.part_id ".
            " WHERE parts.id=". smart_escape($_REQUEST["pid"]).
            " ORDER BY preise.ma DESC LIMIT 1;";
        $result = mysql_query( $query) or die( mysql_error());
        while ( $data = mysql_fetch_assoc( $result))
        {
            print "<tr><td><b>Name:</b></td><td>". smart_unescape( $data['name']) ."</td></tr>". PHP_EOL;
            print "<tr><td><b>Vorhanden:</b></td><td>". smart_unescape( $data['instock']) ."</td></tr>". PHP_EOL;
            print "<tr><td><b>Min. Bestand:</b></td><td>". smart_unescape( $data['mininstock']) ."</td></tr>". PHP_EOL;
            print "<tr><td><b>Footprint:</b></td><td>". smart_unescape( $data['footprint']) ."</td></tr>". PHP_EOL;
            print "<tr><td><b>Lagerort:</b></td><td>". smart_unescape( $data['location']). (( $data['location_is_full'] == 1 ) ? ' [voll]' : '') ."</td></tr>". PHP_EOL;
            print "<tr><td><b>Lieferant:</b></td><td>". smart_unescape( $data['supplier']) ."</td></tr>". PHP_EOL;
            print "<tr><td><b>Bestell-Nr.:</b></td><td>". smart_unescape( $data['supplierpartnr']) ."</td></tr>". PHP_EOL;
            $preis = str_replace('.', ',', $data['preis']);
            print "<tr><td><b>Preis:</b></td><td>". smart_unescape( $preis). PHP_EOL;
            include("config.php");
            print " ".$currency." &nbsp;</td></tr>". PHP_EOL;
            print "<tr><td valign=\"top\"><b>Kommentar:</b></td><td>". nl2br( smart_unescape( $data['comment'])) ."&nbsp;</td></tr>". PHP_EOL;
        }
        ?>
        </table>
        <br>Angaben <a href="editpartinfo.php?pid=<?PHP print $_REQUEST["pid"]; ?>">ver&auml;ndern</a>
        </td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td>
        <table>
        <form action="" method="post">
        <input type="hidden" name="pid" value="<?PHP print $_REQUEST["pid"]; ?>">
        <input type="hidden" name="action" value="r">
        <tr><td colspan="2">Teile entnehmen</td></tr>
        <tr valign="top">
        <td>Anzahl:</td><td><input type="text" size="3" name="n_less" value="1"></td>
        </tr><tr><td colspan="2" align="center"><input type="submit" value="Entnehmen!"></td></tr>
        </form>
        <tr><td colspan="2">&nbsp;</td></tr>
        <form action="" method="post">
        <input type="hidden" name="pid" value="<?PHP print $_REQUEST["pid"]; ?>">
        <input type="hidden" name="action" value="a">
        <tr><td colspan="2">Teile hinzuf&uuml;gen</td></tr>
        <tr valign="top">
        <td>Anzahl:</td><td><input type="text" size="3" name="n_more" value="1"></td>
        </tr><tr><td colspan="2" align="center"><input type="submit" value="Hinzuf&uuml;gen!"></td></tr>
        </form>
        </table>
        </td>
        </tr>
        </table>
        <?PHP
        if (has_image($_REQUEST["pid"]))
        {
        print "<br><b>Bilder:</b><table><tr>\n";
        
        $pict_query = "SELECT pictures.id FROM pictures WHERE (pictures.part_id=". smart_escape($_REQUEST["pid"]) .") AND (pictures.pict_type='P');";
        debug_print ($pict_query);
        $r = mysql_query ($pict_query);

        while ( ($d = mysql_fetch_row ($r)) )
        {
            print "<td><a href=\"javascript:popUp('getimage.php?pict_id=". $d[0] ."')\"><img src=\"getimage.php?pict_id=". $d[0] ."&maxx=200&maxy=150\" alt=\"Zum Vergr&ouml;&szlig;ern klicken!\"></a></td>";
        }
        print "</tr></table>\n";
        }
        ?>
    </div>
</div>


</body>
</html>
