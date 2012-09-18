<?php
class UploadFileForm{    
    function save($path, $name)
    {
		$pref = "";
        $File=$_FILES['qqfile']['tmp_name']; 
        $uploaddir = $_SESSION['path'];
		copy($File, $name);
        $File_new=$path;
        $max_width=500;
        $max_height=500;
        $gab =  getimagesize($File);
        $w=450;
        $h=450;
		require_once('imageresizer.class.php');
		$resizer = new ImageResizer($path, $path, $pref.$uploaddir, $name);
		$resizer->resizeImage($max_width, $max_height, $path, $name);
		$resizer->showResizedImage();
        switch($gab['mime'])
        {
            case 'image/pjpeg'        : $resimage = imagecreatefromjpeg($File); break;
            case 'image/jpeg'        : $resimage = imagecreatefromjpeg($File); break;
            case 'image/gif'        : $resimage = imagecreatefromgif($File); break;
            case 'image/png'        : $resimage = imagecreatefrompng($File); break;
            case 'image/x-ms-bmp'    : $resimage = imagecreatefromwbmp($File); break;
            default                    : $resimage = imagecreatefromjpeg($File); break;
        }
                
        if ($gab[0]>=$w&&$gab[0]>$gab[1])
        {
            $h = ($gab[1]/($gab[0]/$w));
            $smallimage =  imagecreatetruecolor($max_width, $max_height);
            imagefill($smallimage, 0, 0, 0xffffff);
            imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
            imagejpeg($smallimage,$File_new, 100);
        }
        elseif($gab[1]>=$w&&$gab[0]<$gab[1])
        {
            $w = ($gab[0]/($gab[1]/$h));
            $smallimage =  imagecreatetruecolor($max_width, $max_height);
            imagefill($smallimage, 0, 0, 0xffffff);
            imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
            imagejpeg($smallimage,$File_new, 100);
        }
        elseif($gab[1]>=$w)
        {
            $h = ($gab[1]/($gab[0]/$w));
            $smallimage =  imagecreatetruecolor($max_width, $max_height);
            imagefill($smallimage, 0, 0, 0xffffff);
            imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
            imagejpeg($smallimage, $File_new, 100);
        }
        else{
            $smallimage =  imagecreatetruecolor($max_width, $max_height);
            imagefill($smallimage, 0, 0, 0xffffff);
            imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $gab[0], $gab[1], $gab[0], $gab[1]);
            imagejpeg($smallimage, $File_new, 100);
        }
    }

    function getName()
    {
        return $_FILES['qqfile']['name'];
    }
}
?>