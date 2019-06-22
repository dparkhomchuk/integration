<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 26.01.2019
 * Time: 13:19
 */

interface iFileRepository
{
	public function saveFile($integrationId, $file);
	public function getFilesByIntegrationId($integrationId);
}