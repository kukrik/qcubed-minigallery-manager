<?php

    require_once('../../../../../qcubed.inc.php');

    header("Content-Type: application/json"); // Advise a client of a response type

    $arrFolders = [];
    $arrFiles = [];
    $sortFolders = [];
    $sortFiles = [];

    $objFolders = Folders::LoadAll();

    foreach ($objFolders as $objFolder) {
        $arrFolders[] = getFolderParam($objFolder);
    }

    foreach ($arrFolders as $key => $val) {
        $sortFolders[$key] = strtolower($val['path']);
    }
    array_multisort($sortFolders, SORT_ASC, $arrFolders);

    $objFiles = Files::LoadAll();

    foreach ($objFiles as $objFile) {
        $arrFiles[] = getFileParam($objFile);
    }

    foreach ($arrFiles as $key => $val) {
        $sortFiles[$key] = $val['path'];
    }
    array_multisort($sortFiles, SORT_ASC, $arrFiles);

    /**
     * Retrieves the parameters of a folder object as an associative array.
     *
     * @param object $objItem An object representing a folder, providing access to its attributes through methods.
     *
     * @return array An associative array containing the folder's details, including its ID, parent ID, name, type, path,
     * modification time, locked status, and activity lock status.
     */
    function getFolderParam(object $objItem): array
    {
        return [
            'id' => $objItem->getId(),
            'parent_id' => $objItem->getParentId(),
            'name' => $objItem->getName(),
            'type' => $objItem->getType(),
            'path' => $objItem->getPath(),
            'mtime' => $objItem->getMtime(),
            'locked_file' => $objItem->getLockedFile(),
            'activities_locked' => $objItem->getActivitiesLocked()
        ];
    }

    /**
     * Retrieves the parameters of a file from the given object.
     *
     * @param object $objItem The file object providing access to its attributes and properties through defined methods.
     *
     * @return array An associative array containing file-related details such as ID, folder ID, name, type, path,
     *               description, extension, MIME type, size, modification time, dimensions, locked state,
     *               and activity lock status.
     */
    function getFileParam(object $objItem): array
    {
        return [
            'id' => $objItem->getId(),
            'folder_id' => $objItem->getFolderId(),
            'name' => $objItem->getName(),
            'type' => $objItem->getType(),
            'path' => $objItem->getPath(),
            'description' => $objItem->getDescription(),
            'extension' => $objItem->getExtension(),
            'mime_type' => $objItem->getMimeType(),
            'size' => $objItem->getSize(),
            'mtime' => $objItem->getMTime(),
            'dimensions' => $objItem->getDimensions(),
            'locked_file' => $objItem->getLockedFile(),
            'activities_locked' => $objItem->getActivitiesLocked()
        ];
    }

    /**
     * Scans through provided folders and files, organizing the data into a structured format.
     *
     * @param array $folders An array of folder data, where each folder contains information such as parent ID, name, type, path, and metadata.
     * @param array $files An array of file data, used to filter and organize items within folders.
     *
     * @return array Returns a structured array containing folder data with subitems filtered and organized accordingly.
     */
    function scan(array $folders, array $files): array
    {
        $vars = [];

        foreach ($folders as $value) {
            if ($value["parent_id"] !== $value["id"]) {
                $vars[] = [
                    'id' => $value["id"],
                    'parent_id' => $value["parent_id"],
                    'name' => $value["name"],
                    'type' => $value["type"],
                    'path' => $value["path"],
                    'mtime' => $value["mtime"],
                    'locked_file' => $value["locked_file"],
                    'activities_locked' => $value["activities_locked"],
                    'items' => filter($value["id"], $folders, $files)
                ];
            }
        }
        return $vars;
    }

    /**
     * Filters the provided folders and files based on the specified ID.
     *
     * @param int|string $id The ID used to filter folders and files.
     * @param array $folders An array of folder data, where each folder is represented as an associative array.
     * @param array $files An array of file data, where each file is represented as an associative array.
     *
     * @return array An array of filtered folders and files that match the specified ID, including their details.
     */
    function filter(int|string $id, array $folders, array $files): array
    {
        $vars = [];

        foreach ($folders as $value) {
            if ($value["type"] === "dir") {
                if ($id === $value["parent_id"]) {
                    $vars[] = [
                        'id' => $value["id"],
                        'parent_id' => $value["parent_id"],
                        'name' => $value["name"],
                        'type' => $value["type"],
                        'path' => $value["path"],
                        'mtime' => $value["mtime"],
                        'locked_file' => $value["locked_file"],
                        'activities_locked' => $value["activities_locked"]
                    ];
                }
            }
        }
        foreach ($files as $value) {
            if ($value["type"] === "file") {
                if ($id === $value["folder_id"]) {
                    $vars[] = [
                        'id' => $value["id"],
                        'folder_id' => $value["folder_id"],
                        'name' => $value["name"],
                        'type' => $value["type"],
                        'path' => $value["path"],
                        'description' => $value["id"],
                        'extension' => $value["extension"],
                        'mime_type' => $value["mime_type"],
                        'size' => $value["size"],
                        'mtime' => $value["mtime"],
                        'dimensions' => $value["dimensions"],
                        'locked_file' => $value["locked_file"],
                        'activities_locked' => $value["activities_locked"]
                    ];
                }
            }
        }
        return $vars;
    }
    print json_encode(scan($arrFolders, $arrFiles));
