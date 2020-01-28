<?php

namespace WkUploader\FileUpload\Contracts;

/**
 * Interface  UploadsInterface
 *
 * @author   Vaishnavi R  <vaishnavi.r@impelsys.com>
 */
interface UploadsInterface
{

    /**
     * Function to save the uploaded chunk files to temp repo
     *
     * @author Nabin Das <vaishnavi.r@impelsys.com>
     * @param string $destinationPath
     * @param \App\Utilities\RequestInterface $request
     */
    public function saveUploadedFile(string $destinationPath, \RequestInterface $request = null): array;
        
    public function echoPhrase(string $phrase): string ;
}