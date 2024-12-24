<?php
require 'inc/_global/config.php';
require 'inc/backend/config.php';
require 'inc/_global/views/head_start.php';
require 'inc/_global/views/head_end.php';
require 'inc/_global/user.php';
require 'inc/_global/token.php';
require 'inc/_global/views/page_start.php';
$one->get_css('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css');
$one->get_css('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css');
$one->get_css('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css');
?>
<style>
table.table-bordered>tbody>tr>td {
    border: 1px solid #364054;
}

table.table-bordered>tbody>tr>th {
    border: 1px solid #364054;
}
</style>
<link rel="stylesheet" href="assets/css/relaxservices.css">
<div class="content">
    <h2 class="d-print-none">Sülale Sorgu</h2>
    <div class="row">
  <form method="post">
        <div class="col-md-12">
            <div class="col-lg-8 col-xl-5">
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">TCKN</span>
                        <input id="ad" name="ad" type="text" placeholder="123456789xx" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="col-lg-8 col-xl-5">
                <div class="mb-4">
                    <input class="btn btn-secondary" type="submit" name ="ara" value="Sorgula">
                </div>
            </div>
        </div>
		</form>
    </div>
    <div class="row gx-12">
        <div class="col-xl-12 col-lg-6">
            <div class="table-responsive">
                <table id="t" class="table table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>YAKINLIK DURUM</th>
                            <th>TCKN</th>
                            <th>AD SOYAD</th>                                      
                            <th>DOĞUM TARİH</th>                                                                   
                        </tr>
                    </thead>

        <tbody>
           <?php
            $baglanti = new mysqli('localhost', 'root', '', '101m');
            if (isset($_POST["ara"])) {
				 
                $str = $_POST["ad"];
				$bunlariIcerme = array("\\", "\0", "\n", "\r", "'", '"', "\x1a");
				$str = str_replace($bunlariIcerme, '', $str);
                $sth = $baglanti->prepare("SELECT * FROM `101m`");
			$sql = "SELECT * FROM `101m` WHERE `TC` = '$str'";
			$result = $baglanti->query($sql);
      $notify .= "One.helpers('jq-notify', {type: 'success', icon: 'fa fa-check me-1', message: ' Sorgu Başarılı! '});";   
                                      
			while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td> KENDİSİ </td>
                    <td>" . $row["TC"] . "</td>
                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                   <td>" . $row["DOGUMTARIHI"] . "</td>                  
                </tr>";
                $sqlcocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                $resultcocugu = $baglanti->query($sqlcocugu);

                $sqlkardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                $resultkardesi = $baglanti->query($sqlkardesi);
                $sqlbabasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                $resultbabasi = $baglanti->query($sqlbabasi);
                $sqlanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                $resultanasi = $baglanti->query($sqlanasi);

                $sqlkendicocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                $resultkendicocugu = $baglanti->query($sqlkendicocugu);
                while($row = $resultkendicocugu->fetch_assoc()) {
                    echo "<tr>
                        <td> ÇOCUĞU </td>
                        <td>" . $row["TC"] . "</td>
                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                           <td>" . $row["DOGUMTARIHI"] . "</td>
                     
                    </tr>";
                    $sqlkendikendicocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                    $resultkendikendicocugu = $baglanti->query($sqlkendikendicocugu);    
                    while($row = $resultkendikendicocugu->fetch_assoc()) {
                        echo "<tr>
                            <td> TORUNU </td>
                            <td>" . $row["TC"] . "</td>
                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                        
                        </tr>";
                        $sqlkendikendikendicocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                        $resultkendikendikendicocugu = $baglanti->query($sqlkendikendikendicocugu);    
                        while($row = $resultkendikendikendicocugu->fetch_assoc()) {
                            echo "<tr>
                                <td> TORUNUNUN ÇOCUĞU </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            
                        }
                    }
                }
                while($row = $resultkardesi->fetch_assoc()) {
                    echo "<tr>
                        <td> KARDEŞİ </td>
                        <td>" . $row["TC"] . "</td>
                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                           <td>" . $row["DOGUMTARIHI"] . "</td>
                     
                    </tr>";
                    $sqlkardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                    $resultkardescocugu = $baglanti->query($sqlkardescocugu);
                    while($row = $resultkardescocugu->fetch_assoc()) {
                        echo "<tr>
                            <td> KARDEŞİNİN ÇOCUĞU </td>
                            <td>" . $row["TC"] . "</td>
                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                        
                        </tr>";
                        
                        $sqlkardeskardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                        $resultkardeskardescocugu = $baglanti->query($sqlkardeskardescocugu);    
                        while($row = $resultkardeskardescocugu->fetch_assoc()) {
                            echo "<tr>
                                <td> KARDEŞİNİN TORUNU </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlkardeskardeskardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                            $resultkardeskardeskardescocugu = $baglanti->query($sqlkardeskardeskardescocugu);    
                            while($row = $resultkardeskardeskardescocugu->fetch_assoc()) {
                                echo "<tr>
                                    <td> KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                  
                                  
                                  
                                  
                                  
                                  
                                </tr>";
                                
                            }
                        }
                    }
    
                }
    
                while($row = $resultbabasi->fetch_assoc()) {
                    echo "<tr>
                        <td> BABASI </td>
                        <td>" . $row["TC"] . "</td>
                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                           <td>" . $row["DOGUMTARIHI"] . "</td>
                     
                    </tr>";
                    $sqlbabakardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                    $resultbabakardesi = $baglanti->query($sqlbabakardesi);
                    $sqlbabababasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                    $resultbabababasi = $baglanti->query($sqlbabababasi);
                    $sqlbabaanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                    $resultbabaanasi = $baglanti->query($sqlbabaanasi);
    
                    while($row = $resultbabakardesi->fetch_assoc()) {
                        echo "<tr>
                            <td> BABASININ KARDEŞİ </td>
                            <td>" . $row["TC"] . "</td>
                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                        
                        </tr>";
                        $sqlbabakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                        $resultbabakardescocugu = $baglanti->query($sqlbabakardescocugu);
                        while($row = $resultbabakardescocugu->fetch_assoc()) {
                            echo "<tr>
                                <td> BABASININ KARDEŞİNİN ÇOCUĞU </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlbabakardesbabakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                            $resultbabakardesbabakardescocugu = $baglanti->query($sqlbabakardesbabakardescocugu);    
                            while($row = $resultbabakardesbabakardescocugu->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ KARDEŞİNİN TORUNU </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                  
                                  
                                  
                                  
                                  
                                  
                                </tr>";
                                $sqlbabakardesbabakardesbabakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                $resultbabakardesbabakardesbabakardescocugu = $baglanti->query($sqlbabakardesbabakardesbabakardescocugu);    
                                while($row = $resultbabakardesbabakardesbabakardescocugu->fetch_assoc()) {
                                    echo "<tr>
                                        <td> BABASININ KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                        <td>" . $row["TC"] . "</td>
                                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                    </tr>";
                                    
                                }
                            }

                        }
                    }
            
                        while($row = $resultbabababasi->fetch_assoc()) {
                            echo "<tr>
                                <td> BABASININ BABASI </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlbabakardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                            $resultbabakardesi = $baglanti->query($sqlbabakardesi);
                            $sqlbabababasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                            $resultbabababasi = $baglanti->query($sqlbabababasi);
                            $sqlbabaanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                            $resultbabaanasi = $baglanti->query($sqlbabaanasi);
            
                            while($row = $resultbabakardesi->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ BABASININ KARDEŞİ </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                  
                                  
                                  
                                  
                                  
                                  
                                </tr>";
                                $sqlbabababakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                $resultbabababakardescocugu = $baglanti->query($sqlbabababakardescocugu);
                                while($row = $resultbabababakardescocugu->fetch_assoc()) {
                                    echo "<tr>
                                        <td> BABASININ BABASININ KARDEŞİNİN ÇOCUĞU </td>
                                        <td>" . $row["TC"] . "</td>
                                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                    </tr>";
                                    $sqlbabababakardesbabababakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                    $resultbabababakardesbabababakardescocugu = $baglanti->query($sqlbabababakardesbabababakardescocugu);    
                                    while($row = $resultbabababakardesbabababakardescocugu->fetch_assoc()) {
                                        echo "<tr>
                                            <td> BABASININ BABASININ KARDEŞİNİN TORUNU </td>
                                            <td>" . $row["TC"] . "</td>
                                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                          
                                          
                                          
                                          
                                          
                                          
                                          
                                        </tr>";
                                        $sqlbabababakardesbabababakardesbabababakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                        $resultbabababakardesbabababakardesbabababakardescocugu = $baglanti->query($sqlbabababakardesbabababakardesbabababakardescocugu);    
                                        while($row = $resultbabababakardesbabababakardesbabababakardescocugu->fetch_assoc()) {
                                            echo "<tr>
                                                <td> BABASININ BABASININ KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                                <td>" . $row["TC"] . "</td>
                                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                           
                                            </tr>";
                                            
                                        }
                                    }
                                }
                            }
                
                            while($row = $resultbabababasi->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ BABASININ BABASI </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                  
                                  
                                  
                                  
                                  
                                  
                                </tr>";
                                
                            }
                            while($row = $resultbabaanasi->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ BABASININ ANASI </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                  
                                  
                                  
                                  
                              
                                  
                                </tr>";
                                
                            }

                        }
                        while($row = $resultbabaanasi->fetch_assoc()) {
                            echo "<tr>
                                <td> BABASININ ANASI </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlbabakardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                            $resultbabakardesi = $baglanti->query($sqlbabakardesi);
                            $sqlbabababasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                            $resultbabababasi = $baglanti->query($sqlbabababasi);
                            $sqlbabaanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                            $resultbabaanasi = $baglanti->query($sqlbabaanasi);
            
                            while($row = $resultbabakardesi->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ ANNESİNİN KARDEŞİ </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                                </tr>";
                                $sqlbabaannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                $resultbabaannekardescocugu = $baglanti->query($sqlbabaannekardescocugu);
                                while($row = $resultbabaannekardescocugu->fetch_assoc()) {
                                    echo "<tr>
                                        <td> BABASININ ANNESİNİN KARDEŞİNİN ÇOCUĞU </td>
                                        <td>" . $row["TC"] . "</td>
                                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                    
                                    </tr>";
                                    $sqlbabaannekardesbabaannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                    $resultbabaannekardesbabaannekardescocugu = $baglanti->query($sqlbabaannekardesbabaannekardescocugu);    
                                    while($row = $resultbabaannekardesbabaannekardescocugu->fetch_assoc()) {
                                        echo "<tr>
                                            <td> BABASININ ANNESİNİN KARDEŞİNİN TORUNU </td>
                                            <td>" . $row["TC"] . "</td>
                                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                          
                                        </tr>";
                                        $sqlbabaannekardesbabaannekardesbabaannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                        $resultbabaannekardesbabaannekardesbabaannekardescocugu = $baglanti->query($sqlbabaannekardesbabaannekardesbabaannekardescocugu);    
                                        while($row = $resultbabaannekardesbabaannekardesbabaannekardescocugu->fetch_assoc()) {
                                            echo "<tr>
                                                <td> BABASININ ANNESİNİN KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                                <td>" . $row["TC"] . "</td>
                                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                           
                                            </tr>";
                                            
                                        }
                                    }
                                }

                            }
                
                            while($row = $resultbabababasi->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ ANNESİNİN BABASI </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                </tr>";
                                
                            }
                            while($row = $resultbabaanasi->fetch_assoc()) {
                                echo "<tr>
                                    <td> BABASININ ANNESİNİN ANASI </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                 
                                </tr>";
                                
                            }
    
                        }
                    }
                }
                while($row = $resultanasi->fetch_assoc()) {
                    echo "<tr>
                        <td> ANASI </td>
                        <td>" . $row["TC"] . "</td>
                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                           <td>" . $row["DOGUMTARIHI"] . "</td>
                     
                    </tr>";
                    $sqlannekardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                    $resultannekardesi = $baglanti->query($sqlannekardesi);
                    $sqlannebabasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                    $resultannebabasi = $baglanti->query($sqlannebabasi);
                    $sqlanneanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                    $resultanneanasi = $baglanti->query($sqlanneanasi);
    
                    while($row = $resultannekardesi->fetch_assoc()) {
                        echo "<tr>
                            <td> ANNESİNİN KARDEŞİ </td>
                            <td>" . $row["TC"] . "</td>
                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                        
                        </tr>";
                        $sqlannekardescocugu = "SELECT * FROM `101m` WHERE `BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ";
                        $resultannekardescocugu = $baglanti->query($sqlannekardescocugu);
                        while($row = $resultannekardescocugu->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN KARDEŞİNİN ÇOCUĞU </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlannekardesannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                            $resultannekardesannekardescocugu = $baglanti->query($sqlannekardesannekardescocugu);    
                            while($row = $resultannekardesannekardescocugu->fetch_assoc()) {
                                echo "<tr>
                                    <td> ANNESİNİN KARDEŞİNİN TORUNU </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                 
                                </tr>";
                                $sqlannekardesannekardesannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                $resultannekardesannekardesannekardescocugu = $baglanti->query($sqlannekardesannekardesannekardescocugu);    
                                while($row = $resultannekardesannekardesannekardescocugu->fetch_assoc()) {
                                    echo "<tr>
                                        <td> ANNESİNİN KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                        <td>" . $row["TC"] . "</td>
                                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                    
                                    </tr>";
                                    
                                }
                            }

                        }
                    }
        
                    while($row = $resultannebabasi->fetch_assoc()) {
                        echo "<tr>
                            <td> ANNESİNİN BABASI </td>
                            <td>" . $row["TC"] . "</td>
                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                        
                        </tr>";
                        $sqlbabakardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                        $resultbabakardesi = $baglanti->query($sqlbabakardesi);
                        $sqlbabababasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                        $resultbabababasi = $baglanti->query($sqlbabababasi);
                        $sqlbabaanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                        $resultbabaanasi = $baglanti->query($sqlbabaanasi);
        
                        while($row = $resultbabakardesi->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN BABASININ KARDEŞİ </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlannebabakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                            $resultannebabakardescocugu = $baglanti->query($sqlannebabakardescocugu);
                            while($row = $resultannebabakardescocugu->fetch_assoc()) {
                                echo "<tr>
                                    <td> ANNESİNİN BABASININ KARDEŞİNİN ÇOCUĞU </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                   
                                </tr>";
                                $sqlannebabakardesannebabakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                $resultannebabakardesannebabakardescocugu = $baglanti->query($sqlannebabakardesannebabakardescocugu);    
                                while($row = $resultannebabakardesannebabakardescocugu->fetch_assoc()) {
                                    echo "<tr>
                                        <td> ANNESİNİN BABASININ KARDEŞİNİN TORUNU </td>
                                        <td>" . $row["TC"] . "</td>
                                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                      
                                      
                                    </tr>";
                                    $sqlannebabakardesannebabakardesannebabakardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                    $resultannebabakardesannebabakardesannebabakardescocugu = $baglanti->query($sqlannebabakardesannebabakardesannebabakardescocugu);    
                                    while($row = $resultannebabakardesannebabakardesannebabakardescocugu->fetch_assoc()) {
                                        echo "<tr>
                                            <td> ANNESİNİN BABASININ KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                            <td>" . $row["TC"] . "</td>
                                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                     
                                        </tr>";
                                        
                                    }
                                }

                            }
                        }
            
                        while($row = $resultbabababasi->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN BABASININ BABASI </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            
                        }
                        while($row = $resultbabaanasi->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN BABASININ ANASI </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            
                        }
                    }
                    while($row = $resultanneanasi->fetch_assoc()) {
                        echo "<tr>
                            <td> ANNESİNİN ANASI </td>
                            <td>" . $row["TC"] . "</td>
                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                        
                        </tr>";
                        $sqlannekardesi = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["BABATC"] ."' OR `ANNETC` = '" . $row["ANNETC"] ."' ) ";
                        $resultannekardesi = $baglanti->query($sqlannekardesi);
                        $sqlannebabasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["BABATC"] ."' ";
                        $resultannebabasi = $baglanti->query($sqlannebabasi);
                        $sqlanneanasi = "SELECT * FROM `101m` WHERE `TC` = '" . $row["ANNETC"] ."' ";
                        $resultanneanasi = $baglanti->query($sqlanneanasi);
        
                        while($row = $resultannekardesi->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN ANNESİNİN KARDEŞİ </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            $sqlanneannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                            $resultanneannekardescocugu = $baglanti->query($sqlanneannekardescocugu);
                            while($row = $resultanneannekardescocugu->fetch_assoc()) {
                                echo "<tr>
                                    <td> ANNESİNİN ANNESİNİN KARDEŞİNİN ÇOCUĞU </td>
                                    <td>" . $row["TC"] . "</td>
                                    <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                  
                                  
                                </tr>";
                                $sqlanneannekardesanneannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                $resultanneannekardesanneannekardescocugu = $baglanti->query($sqlanneannekardesanneannekardescocugu);    
                                while($row = $resultanneannekardesanneannekardescocugu->fetch_assoc()) {
                                    echo "<tr>
                                        <td> ANNESİNİN ANNESİNİN KARDEŞİNİN TORUNU </td>
                                        <td>" . $row["TC"] . "</td>
                                        <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                     
                                    </tr>";
                                    $sqlanneannekardesanneannekardesanneannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                    $resultanneannekardesanneannekardesanneannekardescocugu = $baglanti->query($sqlanneannekardesanneannekardesanneannekardescocugu);    
                                    while($row = $resultanneannekardesanneannekardesanneannekardescocugu->fetch_assoc()) {
                                        echo "<tr>
                                            <td> ANNESİNİN ANNESİNİN KARDEŞİNİN TORUNUNUN ÇOCUĞU </td>
                                            <td>" . $row["TC"] . "</td>
                                            <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                   <td>" . $row["DOGUMTARIHI"] . "</td>
                                        
                                        </tr>";
                                        $sqlanneannekardesanneannekardesanneannekardesanneannekardescocugu = "SELECT * FROM `101m` WHERE NOT `TC` = '". $row["TC"] ."'  AND (`BABATC` = '" . $row["TC"] ."' OR `ANNETC` = '" . $row["TC"] ."' ) ";
                                        $resultanneannekardesanneannekardesanneannekardesanneannekardescocugu = $baglanti->query($sqlanneannekardesanneannekardesanneannekardesanneannekardescocugu);    
                                        while($row = $resultanneannekardesanneannekardesanneannekardesanneannekardescocugu->fetch_assoc()) {
                                            echo "<tr>
                                                <td> ANNESİNİN ANNESİNİN KARDEŞİNİN TORUNUNUN TORUNU </td>
                                                <td>" . $row["TC"] . "</td>
                                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                           
                                            </tr>";
                                            
                                    }

                                }
                            }

                        }
            
                        while($row = $resultannebabasi->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN ANNESİNİN BABASI </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                            
                        }
                        while($row = $resultanneanasi->fetch_assoc()) {
                            echo "<tr>
                                <td> ANNESİNİN ANNESİNİN ANASI </td>
                                <td>" . $row["TC"] . "</td>
                                <td>" . $row["ADI"] . " " . $row["SOYADI"] . "</td>
                                           <td>" . $row["DOGUMTARIHI"] . "</td>
                                
                            </tr>";
                        }
                        }
                    }
    
                }
            }

        
            ?>
        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require 'inc/_global/views/page_end.php';
require 'inc/_global/views/footer_start.php';
$one->get_js('js/lib/jquery.min.js');
$one->get_js('js/plugins/datatables/jquery.dataTables.min.js');
$one->get_js('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js');
$one->get_js('js/plugins/datatables-responsive/js/dataTables.responsive.min.js');
$one->get_js('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js');
$one->get_js('js/plugins/datatables-buttons/dataTables.buttons.min.js');
$one->get_js('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js');
$one->get_js('js/plugins/datatables-buttons-jszip/jszip.min.js');
$one->get_js('js/plugins/datatables-buttons-pdfmake/pdfmake.min.js');
$one->get_js('js/plugins/datatables-buttons-pdfmake/vfs_fonts.js');
$one->get_js('js/plugins/datatables-buttons/buttons.html5.min.js');
$one->get_js('js/plugins/datatables-buttons/buttons.print.min.js');
?>
<script>
function tcvalidate(tcno) {
    tcno = String(tcno);
    if (tcno.substring(0, 1) === '0') {
        return !1
    }
    if (tcno.length !== 11) {
        return !1
    }
    var ilkon_array = tcno.substr(0, 10).split('');
    var ilkon_total = hane_tek = hane_cift = 0;
    for (var i = j = 0; i < 9; ++i) {
        j = parseInt(ilkon_array[i], 10);
        if (i & 1) {
            hane_cift += j
        } else {
            hane_tek += j
        }
        ilkon_total += j
    }
    if ((hane_tek * 7 - hane_cift) % 10 !== parseInt(tcno.substr(-2, 1), 10)) {
        return !1
    }
    ilkon_total += parseInt(ilkon_array[9], 10);
    if (ilkon_total % 10 !== parseInt(tcno.substr(-1), 10)) {
        return !1
    }
    return !0
}
$('input.tcNumber').on('input', function() {
    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
});

$(document).ready(function() {
    $("#tc").attr('maxlength', '11');
});


function sorgula() {
    var tc = $('#tc').val();
    if (tc.length == 11) {
        if (tcvalidate(tc)) {
            One.helpers('jq-notify', {
                type: 'info',
                icon: 'fa fa-info-circle me-1',
                message: `${tc} Sorgulanıyor...`
            });
            $.ajax({
                type: 'POST',
                url: "api/sorgu.jsp",
                headers: {
                    'Content-Type': 'application/json',
                    'JspCsrf': '<?= token($sessionExpire) ?>',
                    'Action': 'Aile-Sorgu',
                    'Tc': tc
                },
                success: function(data) {
                    $.each(data, function(i, data) {
                        var body = "<tr>";
                        body += "<td>" + data.tc + "</td>";
                        body += "<td>" + data.adSoyad + "</td>";
                        body += "<td>" + data.yakinlik + "</td>";
                        body += "<td>" + data.telefon + "</td>";
                        body += "</tr>";
                        $("#t tbody").append(body);
                    });
					$('#t').append('<caption style="caption-side: bottom">relaxservices</caption>');
 
                    var table = $("#t").DataTable({
						responsive: true,
						buttons: [
							{
								extend: 'copy',
								text: 'Kopyala',
								className: 'btn btn-default btn-xs'
							}
							
						],
                        language: {
                            url: 'assets/json/turkish.json'
                        },
                        dom: 'Bfrtip',
                        processing: true,
                        "paging": false,
                        retrieve: true,
                    });
                    One.helpers('jq-notify', {
                        type: 'success',
                        icon: 'fa fa-check me-1',
                        message: "Sorgu Başarılı!"
                    });
                },
                error: function(response) {
                    var status = response.status;
                    var data = JSON.parse(response.responseText);
                    if (status == 404) {
                        $('.notifyjs-corner').empty();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: data.message
                        });
                    } else if (status == 401) {
                        $('.notifyjs-corner').empty();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: data.message
                        });
                    } else if (status == 402) {
                        $('.notifyjs-corner').empty();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: data.message
                        });
                    } else if (status == 403) {
                        $('.notifyjs-corner').empty();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: data.message
                        });
                    } else if (status == 429) {

                        $('.notifyjs-corner').empty();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: data.message
                        });
                    }
                },
                cache: false
            });

        } else {
            One.helpers('jq-notify', {
                type: 'warning',
                icon: 'fa fa-exclamation me-1',
                message: 'Geçerli Bir Tc Kimlik Numarası Giriniz!'
            });
        }
    } else {
        One.helpers('jq-notify', {
            type: 'warning',
            icon: 'fa fa-exclamation me-1',
            message: 'Tc Kimlik Numarası 11 Haneden Küçük Olamaz!'
        });
    }
}
</script>
<script>
$(document).ready(function() {
    $('#t').DataTable({
        language: {
            url: 'assets/json/turkish.json'
        },
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
        responsive: true,
        processing: true
    });
});
</script>

<?php require 'inc/_global/views/footer_end.php'; ?>
<script>
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            e.preventDefault();
        }
        
        if ((e.ctrlKey && e.key === 'U') || (e.ctrlKey && e.key === 'S') || (e.ctrlKey && e.key === 'P')) {
            e.preventDefault();
        }
    });

    document.onkeydown = function(e) {
        if (e.ctrlKey && e.key === 'U') {
            e.preventDefault();
        }
    };

    document.onselectstart = function() {
        return false;
    };

    document.addEventListener('mousedown', function(e) {
        if (e.button === 2) { 
            e.preventDefault();
        }
        if (e.ctrlKey) { 
            e.preventDefault();
        }
    });

    (function() {
        var devtools = false;
        Object.defineProperty(window, 'devtools', {
            get: function() {
                devtools = true;
            }
        });
    })();

    window.onkeydown = function(e) {
        if (e.key === "F12") {
            e.preventDefault();
        }
    };

    var detectDevTools = setInterval(function() {
        if (window.outerWidth - window.innerWidth > 100 || window.outerHeight - window.innerHeight > 100) {
        }
    }, 1000);
</script>