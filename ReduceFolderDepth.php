<?php
  /*  
   * This script moves all files of deeper folder structure to
   * first level of folder structure.
   * 
   * needed for my MP3 player - to have all *.mp3 files in a single folder for each artist 
   * (Without additional subfolders)
   */

  // config
  $srcDir= "/media/big_boy/media/mp3";
  $dryRun= 1;
  
  // start
  date_default_timezone_set("Europe/Berlin");  
  $dateStr= date("Y-m-d_H-i-s");
  $logfile= "logfile-".$dateStr.".txt";
  
  // rewrite logfile
  file_put_contents( $logfile ,  "---".date("r")."\n");    
  
  function out( $text ){
    global $logfile;
    $text= $text. "\n";
    file_put_contents( $logfile ,  $text, FILE_APPEND);    
  }
  
  /*
   * returns a list of pairs (src, dst)
   * 
   */
  function map_directory_recursive( $currentDir, $firstLevelDir, $dir_depth, &$fileList=array() ){
      
    
    $dir_depth++;
    $directory= dir( $currentDir );
    
    // with unset firstLevelDir - we leave files where they are.
    if( $firstLevelDir== ""){
      $firstLevelDir= $currentDir;
    }
    
    out ( "Path: " . $directory->path );

    while (false !== ($filename = $directory->read())) {
      // skip if . or ..
      if (($filename== ".") || ($filename=="..")) { continue; }
      
      
      // make it a full directory
      $fullDir= $currentDir.'/'.$filename;

      // skip if link
      if (is_link($fullDir)) { continue; }
      
      // recurse if directory - dont forget to add the $root here
      if (is_dir($fullDir)){
        // set directory
        if ($dir_depth == 1){
          $firstLevelDir= $fullDir; 
        }        
        //debug($dir_from_root);
        map_directory_recursive($fullDir, $firstLevelDir, $dir_depth, $fileList);
        continue;
      }
      
      // else insert into list
      $src= $fullDir;
      $dst= $firstLevelDir. "/". $filename;
   
      $fileList[]= array( "src"=>$src, "dst"=>$dst );
      
    }

    $directory->close();
  }  
  
  // start ...
  $fileList= array();
  map_directory_recursive( $srcDir, "", 0, $fileList );

  // move files
  $successCount= 0;
  $errorCount= 0;
  foreach ( $fileList as $pair){
    $src= $pair["src"];
    $dst= $pair["dst"];

    out ( "[src:]". $src );
    out ( "[dst:]". $dst );
    
    if (!isset($dryRun)){
      die("please select if in dry-run mode");
    }
    
    if ($dryRun > 0){
      // dont really move files if in dry-run-mode
      $successCount++;
      out ( "--dryrun only--" );

    } else {
      // 
      if ( rename( $src, $dst ) == true ){
        $successCount++;
        out("success");
      } else {
        $errorCount++;
        out("failed");
      }
    }
    out ( "" );

  }

  $line= "---\ntotal ".count($fileList)." sucess:".$successCount." error:".$errorCount."\nThank you.\nbye\n";
  out( $line );
  echo $line;
  

