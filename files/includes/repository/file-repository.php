<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 26.01.2019
 * Time: 13:18
 */

class FileRepository
{
	private $uploadsFolder;

	public function __construct() {
		$this -> uploadsFolder = wp_upload_dir()['basedir'] . "/flexentric_files_uploads/";
	}

	public function saveFile($integrationId, $path) {
		$file_content = file_get_contents($path);
		$file_name = basename($path);
		$upload = wp_upload_bits($file_name, null, $file_content);
		$path_dir = $this->uploadsFolder . $integrationId . "/";
		wp_mkdir_p($path_dir);
		rename($upload['file'], $path_dir.$file_name);
	}

	public function getFilePath($integrationId) {
		$path_dir = $this->uploadsFolder . $integrationId . "/";
		try {
			if (is_dir($path_dir)) {
				$filesInDir = scandir($path_dir,1);
				$file = $filesInDir[0];
				if ($file && $file != "..") {
					return $path_dir . $file;
				} else {
					return null;
				}
			}
			return null;
		} catch(Exception $e) {
			return null;
		}
	}

	public function removeByIntegrationId($integrationId) {
		$path_dir = $this->uploadsFolder . $integrationId . "/";
		self::deleteDir($path_dir);
	}

	public static function deleteDir($dirPath) {
		if (! is_dir($dirPath)) {
			throw new InvalidArgumentException("$dirPath must be a directory");
		}
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				self::deleteDir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}

}