<?php

namespace WkUploader\FileUpload;

use WkUploader\FileUpload\Contracts\UploadsInterface;
use Flow\Config as FlowConfig;
use Flow\File as FlowFile;
use Flow\Request as FlowRequest;
use Psr\Log\LoggerInterface;

class Uploads implements UploadsInterface
{

    /**
     * @var \MonologServiceProvider
     */
    private $logger;

    /**
     *
     * @var type
     */
    private $file;

    /**
     *
     * @var type
     */
    private $config;

    /**
     *
     * @var type
     */
    private $flowRequest;

    /**
     * @var type
     */

    /**
     *
     * @param string $destinationPath
     * @param \App\Utilities\RequestInterface $request
     * @return array
     */
    public function saveUploadedFile(string $destinationPath, \RequestInterface $request = null): array
    {
        $response = array();

        try {
            $this->config = new FlowConfig();

            $this->config->setTempDir($destinationPath . '/temp');

            $this->file = new FlowFile($this->config);
        } catch (\Exception $ex) {
            $this->logger->writeLog('ERROR', 'Error in creatting flow js library object' . $ex->getMessage());
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $chunkResponse = $this->verifyFileChunk();
                if (!empty($chunkResponse)) {
                    return $chunkResponse;
                }
            } else {
                $validateResp = $this->validateFileChunk();
                if ($validateResp === false) {
                    header("HTTP/1.1 400 Bad Request");
                    return array('isUploaded' => false, 'errors' => 'Bad Request');
                }
                $saveResp = $this->saveFile();
                if ($saveResp === false) {
                    return array('isUploaded' => false, 'resourceId' => null, 'errors' => 'Failed to upload');
                }
            }
        } catch (\Exception $ex) {
            $this->logger->writeLog('ERROR', 'Error in validating the file chunk' . $ex->getMessage());
        }

        try {
            $this->flowRequest = new FlowRequest();
            // get the original file name
            $originalFileName = $this->flowRequest->getFileName();
            // get the file extension
            $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            if ($fileExtension == "") {
                $fileExtension = pathinfo($this->flowRequest->getFile()['name'], PATHINFO_EXTENSION);
            }

            // getting the file details and the priginal filename without extension
            $fileDetails = pathinfo($originalFileName);
            $inutFileName = $fileDetails['filename'];

            $response = array('chunk_number' => $this->flowRequest->getCurrentChunkNumber(), 'is_final_chunk' => 0, 'resource_id' => '', 'isUploaded' => true);
        } catch (\Exception $ex) {
            $this->logger->writeLog('ERROR', 'Error in getting the file details from request ' . $ex->getMessage());
        }

        // validate file
        try {
            if ($this->file->validateFile()) {
                $this->logger->writeLog('INFO', '>>> File validation successful');
                // creating the unique file name for storing
                $resourceId = $inutFileName . '_' . time() . "." . $fileExtension;
                if ($this->file->save($destinationPath . DIRECTORY_SEPARATOR . $resourceId)) {
                    $filePath = $destinationPath . DIRECTORY_SEPARATOR . $resourceId;
                    $response['is_final_chunk'] = 1;
                    $response['resource_id'] = $resourceId;
                    $response['resourceId'] = $resourceId;
                    $response['filePath'] = $filePath;
                    $this->logger->writeLog('INFO', '>>> File saved successfully.. returning response = [' . serialize($response) . ']');

                    return $response;
                }
            }
            return $response;
        } catch (\Exception $ex) {
            $this->logger->writeLog('ERROR', 'Error in validating and saving the file ' . $ex->getMessage());
        }

        return array('isUploaded' => false, 'resourceId' => null, 'errors' => 'Failed to upload');
    }

    /**
     *
     * @return type
     */
    private function verifyFileChunk()
    {
        if ($this->file->checkChunk()) {
            header("HTTP/1.1 200 Ok");
        } else {
            header("HTTP/1.1 204 No Content");
            return array('isUploaded' => false, 'errors' => 'No Content');
        }
    }

    /**
     *
     * @return type
     */
    private function validateFileChunk()
    {
        $response = $this->file->validateChunk();
        return $response;
    }

    /**
     *
     * @return type
     */
    private function saveFile()
    {
        $response = $this->file->saveChunk();
        return $response;
    }

    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function echoPhrase(string $phrase): string
    {
        return $phrase;
    }

}
