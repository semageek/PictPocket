<?php
/******************************************************************************/
/*                                                                            */
/*                       __        ____                                       */
/*                 ___  / /  ___  / __/__  __ _____________ ___               */
/*                / _ \/ _ \/ _ \_\ \/ _ \/ // / __/ __/ -_|_-<               */
/*               / .__/_//_/ .__/___/\___/\_,_/_/  \__/\__/___/               */
/*              /_/       /_/                                                 */
/*                                                                            */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Titre          : Ajouter automatiquement un logo en filigrane...           */
/*                                                                            */
/* URL            : http://www.phpsources.org/scripts398-PHP.htm              */
/* Auteur         : lamogere                                                  */
/* Date édition   : 29-05-2008                                                */
/* Website auteur : http://www.toplien.fr/                                    */
/*                                                                            */
/******************************************************************************/

/* ne pas oublier d'ajouter les lignes suivantes dans le fichier .htaccess :
addhandler WaterMark jpg gif jpeg png
action WaterMark /watermark.php
*/

// répertoire contenant le cache
//define('CACHE_PATH', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/cache');
// chemin du logo à ajouter en filigrane (watermark)
//define('LOGO_PATH', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/stamp.png');

//$imagePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $_SERVER['REQUEST_URI'];
/*$watermarker = new Watermarker(LOGO_PATH, 
                                               POSITION_BOTTOM, POSITION_RIGHT,
   	                                       10, 10, 
                                               75);*/
//$watermarker->display($imagePath, 9, CACHE_PATH);

// à partir d'ici vous n'avez normalement rien à changer
 define('POSITION_TOP', -1);
 define('POSITION_MIDDLE', 0);
 define('POSITION_BOTTOM', 1);
 define('POSITION_LEFT', -1);
 define('POSITION_RIGHT', 1);

class Watermarker {

	var $stampPath = null;
	var $vAlign = POSITION_LEFT;
	var $hAlign = POSITION_TOP;
	var $vMargin = 5;
	var $hMargin = 5;
	var $alpha = 100;


	function Watermarker($stampPath, //chemin du logo
				      $vAlign=POSITION_TOP,  // alignement vertical du logo
				      $hAlign=POSITION_LEFT, // alignement horizontal du logo
				      $vMargin = 5,   //marge verticale du logo
				      $hMargin = 5, // marge horizontale du logo
				      $transparence=100) { // pourcentage de transparence du logo
		$this->stampPath = $stampPath;
		$this->vAlign = $vAlign;
		$this->hAlign = $hAlign;
		$this->alpha = $transparence/100;
	}

	function openImage($path) {
		if ($original_image_info = getimagesize($path)) {
			$original_image_imagetype = $original_image_info[2];
			if ($original_image_imagetype == IMAGETYPE_GIF) {
				return imagecreatefromgif($path);
			} 
			elseif ($original_image_imagetype == IMAGETYPE_JPEG) {
				return imagecreatefromjpeg($path);
			} 
			elseif ($original_image_imagetype == IMAGETYPE_PNG) {
				return imagecreatefrompng($path);
			} else {
				return false;
			}
		} else {
			$ext = strtolower(substr($path, strrpos($path, '.') + 1));
			switch($ext){
				case 'png' :
					return imagecreatefrompng($path);
					break;
				case 'gif' :
					return imagecreatefromgif($path);
					break;
				case 'jpg' :
				case 'jpeg' :
					return imagecreatefromjpeg($path);
					break;
				default :
					return false;
			}
		}
	}

	// On donne l'image d'origine et le tampon à apposer
	function makeOutput($imagePath){
		// Ouverture des images
		if(!($image = $this->openImage($imagePath))){
			return false;
		}
		if(!($stamp = $this->openImage($this->stampPath))){
			return false;
		}
		// Dimension des images
		$imageWidth = imagesx($image);
		$imageHeight = imagesy($image);
		$stampWidth = imagesx($stamp);
		$stampHeight = imagesy($stamp);

		// Calcul de la position
		if($this->hAlign==POSITION_MIDDLE){
			$stampX = floor(($imageWidth/2)-($stampWidth/2));
		}elseif($this->hAlign==POSITION_RIGHT){
			$stampX = $imageWidth-$stampWidth-$this->hMargin;
		} else {
			$stampX = $this->hMargin;
		}

		if($this->vAlign==POSITION_MIDDLE){
			$stampY = floor(($imageHeight/2)-($stampHeight/2));
		}elseif($this->vAlign==POSITION_BOTTOM){
			$stampY = $imageHeight-$stampHeight-$this->vMargin;
		} else {
			$stampY = $this->vMargin;
		}

		// On crée l'image de sortie
		$output = imagecreatetruecolor($imageWidth, $imageHeight);
		
		//copie l'image de fond
		imagecopy($output, $image, 0, 0, 0, 0, $imageWidth, $imageHeight);
		imagedestroy($image);

		//copie le watermark
		for($y=$stampY; $y<$stampY+$stampHeight; $y++){
			for($x=$stampX; $x<$stampX+$stampWidth; $x++){

				$RGB = imagecolorat($output, $x, $y);

				// Calcul de la poisition correspondante sur le tampon
				$x2	= $x - $stampX;
				$y2	= $y - $stampY;

				// On récupère la couleur du pixel courant sur l'image
				$RGB = imagecolorsforindex($output, $RGB);
				// On récupère la couleur du pixel courant sur le tampon
				$stampRGB = imagecolorsforindex($stamp, 
									      imagecolorat($stamp, $x2, $y2));

				// Lecture des données de transparence
				$stampAlpha = round(((127-$stampRGB['alpha'])/127), 2 )* 
				                     $this->alpha;

				// Mélange de couleur de pixels de l'image et du tampon
				$RGB['red'] = round((($RGB['red']*(1-$stampAlpha))+
				                            ($stampRGB['red']*$stampAlpha)));
				$RGB['green'] = round((($RGB['green']*(1-$stampAlpha))+
				                               ($stampRGB['green']*$stampAlpha)));
				$RGB['blue'] = round((($RGB['blue']*(1-$stampAlpha))+
				                             ($stampRGB['blue']*$stampAlpha)));

				$RGB = imagecolorexact($output, $RGB['red'], 
				                                             $RGB['green'], 
									     $RGB['blue']);
				if ($RGB < 0) {
					$RGB = imagecolorallocate($output, $RGB['red'], 
					                                                $RGB['green'], 
					                                                $RGB['blue']);
				}
				if ($RGB < 0) {
					$RGB = imagecolorclosest($output, $RGB['red'], 
					                                                $RGB['green'], 
					                                                $RGB['blue']);
				}

				// Dessine le pixel
				imagesetpixel($output, $x, $y, $RGB);
			}
		}

		imagedestroy($stamp);

		return $output;
	}

	function display($imagePath, //chemin de l'image
			      $quality=9, // 0 = pas de compression -> 9 = compression maximum
			      $cachePath = null) { //répertoire cache
		if ($cachePath) {
			$cacheName = $cachePath . '/' . md5($imagePath) . '.png';
			if (!file_exists($cacheName) ||
			    (@filemtime($cacheName) < @filemtime($imagePath)) || 
			    (@filemtime($cacheName) < @filemtime($this->stampPath))) {
				imagepng($this->makeOutput($imagePath), $cacheName, $quality);
			}
			header('Content-Type: image/png');
			readfile($cacheName);
		} else {
			header('Content-Type: image/png');
			imagepng($this->makeOutput($imagePath));
		}
	}
}
?>