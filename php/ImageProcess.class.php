<?php 
class ImageProcess{
	// 生成图片的宽度 
	static public $RESIZEWIDTH=400; 
	// 生成图片的高度 
	static public $RESIZEHEIGHT=400; 

	private function ResizeImage($im,$maxwidth,$maxheight,$saveto){ 
		$width = imagesx($im); 
		$height = imagesy($im); 
		
		if(($maxwidth && $width > $maxwidth) || ($maxheight && $height > $maxheight)){ 
			if($maxwidth && $width > $maxwidth){ 
				$widthratio = $maxwidth/$width; 
				$RESIZEWIDTH=true; 
			} 
		
			if($maxheight && $height > $maxheight){ 
				$heightratio = $maxheight/$height; 
				$RESIZEHEIGHT=true; 
			} 
		
			if($RESIZEWIDTH && $RESIZEHEIGHT){ 
				if($widthratio < $heightratio){ 
					$ratio = $widthratio; 
				}else{ 
					$ratio = $heightratio; 
				} 
			}elseif($RESIZEWIDTH){ 
				$ratio = $widthratio; 
			}elseif($RESIZEHEIGHT){ 
				$ratio = $heightratio; 
			} 
		
			$newwidth = $width * $ratio; 
			$newheight = $height * $ratio; 
		
			if(function_exists("imagecopyresampled")){ 
				$newim = imagecreatetruecolor($newwidth, $newheight); 
				imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); 
			}else{ 
				$newim = imagecreate($newwidth, $newheight); 
				imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); 
			} 
		
			ImageJpeg ($newim, $saveto); 
			ImageDestroy ($newim); 
		}else{
			ImageJpeg ($im, $saveto); 
		} 
	}
	
	public function genThumb( $source, $saveto ){
		$im = imagecreatefromjpeg( $source ); 
		$this->ResizeImage($im, ImageProcess::$RESIZEWIDTH, ImageProcess::$RESIZEHEIGHT,$saveto); 
		ImageDestroy ($im);
		//$im = imagecreatefrompng( $source ); 
		//$im = imagecreatefrompng( $source ); 
	}
} 

//var_dump( $argv );
if( count($argv) == 3 ){
	$obj = new ImageProcess();
	$obj->genThumb( $argv[1], $argv[2] );
}
?> 