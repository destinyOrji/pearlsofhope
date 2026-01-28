<?php
/**
 * Database connection and helper functions for MongoDB
 */

// Prevent multiple inclusions
if (defined('NGO_DB_LOADED')) {
    return;
}
define('NGO_DB_LOADED', true); 

require_once __DIR__ . '/config.php';

// Load MongoDB library
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php'
];

foreach ($autoloadPaths as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        break;
    }
}

// Global MongoDB client and database variables
$mongoClient = null;
$mongoDatabase = null;

/**
 * Initialize MongoDB connection with fallback options
 * @return MongoDB\Database|null
 */
function initMongoDB() {
    global $mongoClient, $mongoDatabase;
    
    if ($mongoClient !== null) {
        return $mongoDatabase;
    }
    
    // For web requests, prioritize speed - try localhost first with very short timeout
    $isWebRequest = isset($_SERVER['HTTP_HOST']);
    $startTime = microtime(true);
    
    if ($isWebRequest) {
        // Web request - prioritize Atlas for production
        $connectionAttempts = [
            // Attempt 1: Atlas (production priority)
            [
                'uri' => MONGODB_URI,
                'options' => [
                    'ssl' => true,
                    'tlsAllowInvalidCertificates' => !IS_PRODUCTION, // Only allow in development
                    'tlsAllowInvalidHostnames' => !IS_PRODUCTION,   // Only allow in development
                    'connectTimeoutMS' => 5000,
                    'serverSelectionTimeoutMS' => 5000,
                    'socketTimeoutMS' => 5000
                ],
                'description' => 'MongoDB Atlas'
            ]
        ];
        
        // Add localhost fallback only in development
        if (!IS_PRODUCTION) {
            $connectionAttempts[] = [
                'uri' => 'mongodb://localhost:27017',
                'options' => [
                    'connectTimeoutMS' => 1000,
                    'serverSelectionTimeoutMS' => 1000,
                    'socketTimeoutMS' => 1000
                ],
                'description' => 'localhost'
            ];
        }
    } else {
        // CLI request - prioritize Atlas for production
        $connectionAttempts = [
            // Attempt 1: Atlas (production priority)
            [
                'uri' => MONGODB_URI,
                'options' => [
                    'ssl' => true,
                    'tlsAllowInvalidCertificates' => !IS_PRODUCTION, // Only allow in development
                    'tlsAllowInvalidHostnames' => !IS_PRODUCTION,   // Only allow in development
                    'connectTimeoutMS' => 15000,
                    'serverSelectionTimeoutMS' => 15000
                ],
                'description' => 'MongoDB Atlas'
            ]
        ];
        
        // Add localhost fallback only in development
        if (!IS_PRODUCTION) {
            $connectionAttempts[] = [
                'uri' => 'mongodb://localhost:27017',
                'options' => [
                    'connectTimeoutMS' => 3000,
                    'serverSelectionTimeoutMS' => 3000
                ],
                'description' => 'localhost'
            ];
        }
    }
    
    foreach ($connectionAttempts as $index => $attempt) {
        $attemptStartTime = microtime(true);
        
        try {
            $logPrefix = $isWebRequest ? "[WEB]" : "[CLI]";
            error_log("{$logPrefix} MongoDB connection attempt " . ($index + 1) . " to {$attempt['description']}");
            
            $mongoClient = new MongoDB\Client($attempt['uri'], [], $attempt['options']);
            $mongoDatabase = $mongoClient->selectDatabase(MONGODB_DATABASE);
            
            // Test the connection with ping
            $mongoClient->selectDatabase('admin')->command(['ping' => 1]);
            
            $attemptDuration = round((microtime(true) - $attemptStartTime) * 1000, 2);
            $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
            
            error_log("{$logPrefix} MongoDB connection successful to {$attempt['description']} in {$attemptDuration}ms (total: {$totalDuration}ms)");
            return $mongoDatabase;
            
        } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
            $attemptDuration = round((microtime(true) - $attemptStartTime) * 1000, 2);
            error_log("{$logPrefix} MongoDB connection timeout to {$attempt['description']} after {$attemptDuration}ms: " . $e->getMessage());
            $mongoClient = null;
            $mongoDatabase = null;
            
            // For web requests, if we've exceeded 3 seconds total, stop trying
            if ($isWebRequest && (microtime(true) - $startTime) > 3) {
                error_log("{$logPrefix} Stopping connection attempts - exceeded 3 second web request limit");
                break;
            }
            continue;
            
        } catch (MongoDB\Driver\Exception\ServerSelectionTimeoutException $e) {
            $attemptDuration = round((microtime(true) - $attemptStartTime) * 1000, 2);
            error_log("{$logPrefix} MongoDB server selection timeout to {$attempt['description']} after {$attemptDuration}ms: " . $e->getMessage());
            $mongoClient = null;
            $mongoDatabase = null;
            
            // For web requests, if we've exceeded 3 seconds total, stop trying
            if ($isWebRequest && (microtime(true) - $startTime) > 3) {
                error_log("{$logPrefix} Stopping connection attempts - exceeded 3 second web request limit");
                break;
            }
            continue;
            
        } catch (Exception $e) {
            $attemptDuration = round((microtime(true) - $attemptStartTime) * 1000, 2);
            $errorType = get_class($e);
            error_log("{$logPrefix} MongoDB connection failed to {$attempt['description']} after {$attemptDuration}ms ({$errorType}): " . $e->getMessage());
            $mongoClient = null;
            $mongoDatabase = null;
            continue;
        }
    }
    
    $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
    error_log("{$logPrefix} All MongoDB connection attempts failed after {$totalDuration}ms");
    return null;
}

/**
 * Get MongoDB collection
 * @param string $collectionName
 * @return MongoDB\Collection|null
 */
function getCollection($collectionName) {
    $database = initMongoDB();
    if ($database === null) {
        return null;
    }
    
    try {
        return $database->selectCollection($collectionName);
    } catch (Exception $e) {
        error_log("Error getting collection '$collectionName': " . $e->getMessage());
        return null;
    }
}

/**
 * Insert a single document into collection
 * @param MongoDB\Collection $collection
 * @param array $document
 * @return MongoDB\InsertOneResult|false
 */
function insertDocument($collection, $document) {
    try {
        // Add created_at timestamp if not present
        if (!isset($document['created_at'])) {
            $document['created_at'] = new MongoDB\BSON\UTCDateTime();
        }
        
        return $collection->insertOne($document);
    } catch (Exception $e) {
        error_log("Error inserting document: " . $e->getMessage());
        return false;
    }
}

/**
 * Find multiple documents in collection
 * @param MongoDB\Collection $collection
 * @param array $filter
 * @param array $options
 * @return MongoDB\Driver\Cursor|false
 */
function findDocuments($collection, $filter = [], $options = []) {
    try {
        return $collection->find($filter, $options);
    } catch (Exception $e) {
        error_log("Error finding documents: " . $e->getMessage());
        return false;
    }
}

/**
 * Find a single document in collection
 * @param MongoDB\Collection $collection
 * @param array $filter
 * @param array $options
 * @return array|null|false
 */
function findOneDocument($collection, $filter, $options = []) {
    try {
        $result = $collection->findOne($filter, $options);
        return $result ? $result : null;
    } catch (Exception $e) {
        error_log("Error finding document: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a document in collection
 * @param MongoDB\Collection $collection
 * @param array $filter
 * @param array $update
 * @param array $options
 * @return MongoDB\UpdateResult|false
 */
function updateDocument($collection, $filter, $update, $options = []) {
    try {
        // Add updated_at timestamp to the update operation
        if (isset($update['$set'])) {
            $update['$set']['updated_at'] = new MongoDB\BSON\UTCDateTime();
        } else {
            $update['$set'] = ['updated_at' => new MongoDB\BSON\UTCDateTime()];
        }
        
        return $collection->updateOne($filter, $update, $options);
    } catch (Exception $e) {
        error_log("Error updating document: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a document from collection
 * @param MongoDB\Collection $collection
 * @param array $filter
 * @return MongoDB\DeleteResult|false
 */
function deleteDocument($collection, $filter) {
    try {
        return $collection->deleteOne($filter);
    } catch (Exception $e) {
        error_log("Error deleting document: " . $e->getMessage());
        return false;
    }
}

/**
 * Count documents in collection
 * @param MongoDB\Collection $collection
 * @param array $filter
 * @return int|false
 */
function countDocuments($collection, $filter = []) {
    try {
        return $collection->countDocuments($filter);
    } catch (Exception $e) {
        error_log("Error counting documents: " . $e->getMessage());
        return false;
    }
}

/**
 * Test MongoDB connection
 * @return bool
 */
function testMongoConnection() {
    $startTime = microtime(true);
    $isWebRequest = isset($_SERVER['HTTP_HOST']);
    $logPrefix = $isWebRequest ? "[WEB]" : "[CLI]";
    
    try {
        error_log("{$logPrefix} Testing MongoDB connection...");
        
        $database = initMongoDB();
        if ($database === null) {
            error_log("{$logPrefix} MongoDB connection test failed - initMongoDB returned null");
            return false;
        }
        
        // Try to list collections to test connection
        $collections = $database->listCollections();
        $collectionCount = 0;
        foreach ($collections as $collection) {
            $collectionCount++;
        }
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        error_log("{$logPrefix} MongoDB connection test successful in {$duration}ms - found {$collectionCount} collections");
        return true;
        
    } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        error_log("{$logPrefix} MongoDB connection test failed with timeout after {$duration}ms: " . $e->getMessage());
        return false;
        
    } catch (MongoDB\Driver\Exception\ServerSelectionTimeoutException $e) {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        error_log("{$logPrefix} MongoDB connection test failed with server selection timeout after {$duration}ms: " . $e->getMessage());
        return false;
        
    } catch (Exception $e) {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $errorType = get_class($e);
        error_log("{$logPrefix} MongoDB connection test failed after {$duration}ms ({$errorType}): " . $e->getMessage());
        return false;
    }
}
?>