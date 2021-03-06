<?php

/*
   ------------------------------------------------------------------------
   Projects
   Copyright (C) 2018 by the Stats Development Team.

   https://github.com/JulioAugustoS/stats
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Stats project.

   Stats plugin is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Stats plugin is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Stats plugin. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Stats plugin
   @copyright Copyright (c) 2018 Stats team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/JulioAugustoS/stats
   @since     2018

   ------------------------------------------------------------------------
 */

if(!defined('GLPI_ROOT')):
    die("Sorry. You can't access directly to this file");
endif;

if(!defined("GLPI_STATS_DIR")):
    define("GLPI_STATS_DIR", GLPI_ROOT . "/plugins/stats");
endif;

class PluginStats extends CommonDBTM {

    static function displayStats(){

        global $CFG_GLPI, $DB;

        $userId         = $_SESSION['glpiID'];
        $profileId      = $_SESSION['glpiactiveprofile']['id'];
        $activeEntity   = $_SESSION['glpiactive_entity'];
        $glpiEntity     = $_SESSION['glpiactiveentities'];

        $colorTot = "color: #337AB7";
        $colorFec = "color: #555";
        $colorPro = "color: #49BF8F";
        $colorSol = "color: #000";
        $colorPen = "color: #FFA830";
        $colorDue = "color: #D9534F";

        $queryProfile = "SELECT id, name FROM glpi_profiles WHERE interface <> 'helpdesk'";
        $resProfile = $DB->query($queryProfile);

        while($row = $DB->fetch_assoc($resProfile)):
            $arrPro[] = $row['id'];
        endwhile;

        if(in_array($profileId, $arrPro)){
	
            $entities = Profile_User::getUserEntities($_SESSION['glpiID'], true);
            
            if($activeEntity != 0 and $profileId != "6"){		
                $ent = implode(",", $glpiEntity);
                $entidade = "AND entities_id IN (".$ent.")";
                $getuser = "admin";
            }elseif($profileId == "6"){				
                $ent = $activeEntity;
                $entidade = "AND entities_id IN (".$ent.")";
                $getuser = 6;
            }else{				
                $ent = implode(",", $entities);
                $entidade = "AND entities_id IN (".$ent.")";
                $getuser = 0;
            }	

        }else{
            $ent = implode(",", $glpiEntity);
            $entidade = "AND entities_id IN (".$ent.")";
            $getuser = "AND id IN = " . $userId . "";
        }

        // Total Geral
        $sqlTotal = "SELECT COUNT(id) AS total
                FROM glpi_tickets
                WHERE is_deleted = 0
                AND status NOT IN (6)
                ".$entidade."
            ";
        $resultTotal = $DB->query($sqlTotal) or die('Erro ao retornar o total de chamados');
        $totalGeral = $DB->result($resultTotal, 0, 'total');

        // Total Fechados
        $sqlClosed = "SELECT COUNT(id) AS total
                FROM glpi_tickets
                WHERE is_deleted = 0
                AND status = 6
                ".$entidade."
            ";
        $resultClosed = $DB->query($sqlClosed) or die('Erro ao retornar o total de chamados');
        $totalClosed = $DB->result($resultClosed, 0, 'total');

        // Total Processando
        $sqlPro = "SELECT COUNT(id) AS total
                FROM glpi_tickets
                WHERE is_deleted = 0
                AND status NOT IN (1,4,5,6)
                ".$entidade."
            ";
        $resultPro = $DB->query($sqlPro) or die('Erro ao retornar o total de chamados');
        $totalPro = $DB->result($resultPro, 0, 'total');

        // Total Solucionado
        $sqlSol = "SELECT COUNT(id) AS total
                FROM glpi_tickets
                WHERE is_deleted = 0
                AND status = 5
                ".$entidade."
            ";
        $resultSol = $DB->query($sqlSol) or die('Erro ao retornar o total de chamados');
        $totalSol = $DB->result($resultSol, 0, 'total');

        // Total Pendentes
        $sqlPen = "SELECT COUNT(id) AS total
                FROM glpi_tickets
                WHERE is_deleted = 0
                AND status = 4
                ".$entidade."
            ";
        $resultPen = $DB->query($sqlPen) or die('Erro ao retornar o total de chamados');
        $totalPen = $DB->result($resultPen, 0, 'total');

        // Total Atrasados
        $sqlDue = "SELECT COUNT(id) AS due
                FROM glpi_tickets
                WHERE status NOT IN (4,5,6) 
                AND is_deleted = 0
                AND time_to_resolve IS NOT NULL
                AND time_to_resolve < NOW()
                ".$entidade."
            ";
        $resultDue = $DB->query($sqlDue) or die('Erro ao retornar o total de chamados');
        $totalDue = $DB->result($resultDue, 0, 'due');

        echo '<style>
                #tab_stats {
                    font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; 
                    text-align:center; 
                    margin:auto; 
                    width:90%; 
                    margin-bottom:20px; 
                    background:#fff; 
                    border: 1px solid #ddd; 
                    table-layout: fixed;
                }    
                #tab_stats tr td {
                    padding: 20px;
                }    
                .border {
                    border-right: 1px solid #ddd;
                }
                #tab_stats tr td a {
                    font-size:22pt;
                }
                @media screen and (max-width: 767px){
                    #tab_stats {display:none;}
                }
              </style>';
        echo '<table id="tab_stats">';
        echo '<tr>';
        echo '<td class="border"><span><a style="'.$colorTot.'" href="">' . $totalGeral . '</a> </span> </p><span style="color:#333; font-size:14pt;"> '. _nx('ticket','Opened','Opened',2) . '</span></td>';
        echo '<td class="border"><span><a style="'.$colorFec.'" href="">' . $totalClosed . '</a> </span> </p><span style="color:#333; font-size:14pt;"> '. Ticket::getStatus(6) .'s </span></td>';
        echo '<td class="border"><span><a style="'.$colorPro.'" href="">' . $totalPro . '</a></span> </p><span style="color:#333; font-size:14pt;"> '. __('Processing') . ' </span></td>';
        echo '<td class="border"><span><a style="'.$colorSol.'" href="">' . $totalSol . '</a></span> </p><span style="color:#333; font-size:14pt;"> '. Ticket::getStatus(5) .'</span></td>';
        echo '<td class="border"><span><a style="'.$colorPen.'" href="">' . $totalPen . '</a> </span> </p><span style="color:#333; font-size:14pt;"> '. Ticket::getStatus(4) .' </span></td>';
        echo '<td><span><a style="'.$colorDue.'" href="">' . $totalDue . '</a>  </span> </p><span style="color:#333; font-size:14pt;"> '. __('Late') . ' </span></td>';
        echo '</tr>';
        echo '</table>';

    }

}