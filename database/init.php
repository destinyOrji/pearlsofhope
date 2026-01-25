<?php
/**
 * Database initialization script
 * Creates indexes for all collections and inserts default data
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Create indexes for all collections
 */
function createIndexes() {
    echo "Creating database indexes...\n";
    
    try {
        // Administrators collection indexes
        $adminCollection = getCollection('administrators');
        if ($adminCollection) {
            // Unique index on username
            $adminCollection->createIndex(['username' => 1], ['unique' => true]);
            echo "✓ Created unique index on administrators.username\n";
        }
        
        // Activities collection indexes
        $activitiesCollection = getCollection('activities');
        if ($activitiesCollection) {
            // Index for sorting by creation date (newest first)
            $activitiesCollection->createIndex(['created_at' => -1]);
            echo "✓ Created index on activities.created_at\n";
            
            // Index for filtering by status
            $activitiesCollection->createIndex(['status' => 1]);
            echo "✓ Created index on activities.status\n";
        }
        
        // Pages collection indexes
        $pagesCollection = getCollection('pages');
        if ($pagesCollection) {
            // Unique index on page_name
            $pagesCollection->createIndex(['page_name' => 1], ['unique' => true]);
            echo "✓ Created unique index on pages.page_name\n";
        }
        
        // Contact messages collection indexes (optional)
        $contactCollection = getCollection('contact_messages');
        if ($contactCollection) {
            // Index for sorting by creation date (newest first)
            $contactCollection->createIndex(['created_at' => -1]);
            echo "✓ Created index on contact_messages.created_at\n";
        }
        
        // Team members collection indexes
        $teamCollection = getCollection('team_members');
        if ($teamCollection) {
            // Index for sorting by display order and creation date
            $teamCollection->createIndex(['display_order' => 1, 'created_at' => 1]);
            echo "✓ Created index on team_members.display_order and created_at\n";
            
            // Index for filtering by status
            $teamCollection->createIndex(['status' => 1]);
            echo "✓ Created index on team_members.status\n";
        }
        
        echo "All indexes created successfully!\n\n";
        return true;
        
    } catch (Exception $e) {
        echo "Error creating indexes: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Insert default administrator account
 */
function insertDefaultAdmin() {
    echo "Creating default administrator account...\n";
    
    try {
        $adminCollection = getCollection('administrators');
        if (!$adminCollection) {
            throw new Exception("Could not get administrators collection");
        }
        
        // Check if admin already exists
        $existingAdmin = findOneDocument($adminCollection, ['username' => 'admin']);
        if ($existingAdmin) {
            echo "✓ Default administrator already exists\n";
            return true;
        }
        
        // Create default admin document
        $adminDocument = [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@ngo-website.local',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = insertDocument($adminCollection, $adminDocument);
        if ($result && $result->getInsertedCount() > 0) {
            echo "✓ Default administrator created (username: admin, password: admin123)\n";
            return true;
        } else {
            throw new Exception("Failed to insert administrator document");
        }
        
    } catch (Exception $e) {
        echo "Error creating default administrator: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Insert default page content
 */
function insertDefaultPages() {
    echo "Creating default page content...\n";
    
    try {
        $pagesCollection = getCollection('pages');
        if (!$pagesCollection) {
            throw new Exception("Could not get pages collection");
        }
        
        $defaultPages = [
            [
                'page_name' => 'home',
                'title' => 'Welcome to Our NGO',
                'content' => 'We are dedicated to making a positive impact in our community through various charitable activities and programs. Our mission is to help those in need and create lasting change for a better tomorrow.',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ],
            [
                'page_name' => 'about',
                'title' => 'About Our Organization',
                'content' => 'Founded with the vision of creating positive change, our NGO has been serving the community for years. We focus on education, healthcare, and social welfare programs that directly benefit those who need it most. Our dedicated team of volunteers and staff work tirelessly to ensure that our programs reach the maximum number of beneficiaries.',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ],
            [
                'page_name' => 'contact',
                'title' => 'Contact Us',
                'content' => 'Get in touch with us to learn more about our work or to get involved. We welcome volunteers, donors, and partners who share our vision of making a difference.\n\nAddress: 123 NGO Street, Community City, State 12345\nPhone: (555) 123-4567\nEmail: info@ngo-website.org',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];
        
        foreach ($defaultPages as $page) {
            // Check if page already exists
            $existingPage = findOneDocument($pagesCollection, ['page_name' => $page['page_name']]);
            if ($existingPage) {
                echo "✓ Page '{$page['page_name']}' already exists\n";
                continue;
            }
            
            $result = insertDocument($pagesCollection, $page);
            if ($result && $result->getInsertedCount() > 0) {
                echo "✓ Created default content for '{$page['page_name']}' page\n";
            } else {
                echo "✗ Failed to create '{$page['page_name']}' page\n";
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        echo "Error creating default pages: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Insert sample team members
 */
function insertSampleTeamMembers() {
    echo "Creating sample team members...\n";
    
    try {
        $teamCollection = getCollection('team_members');
        if (!$teamCollection) {
            throw new Exception("Could not get team_members collection");
        }
        
        // Check if team members already exist
        $existingCount = countDocuments($teamCollection);
        if ($existingCount > 0) {
            echo "✓ Team members already exist ($existingCount found)\n";
            return true;
        }
        
        $sampleTeamMembers = [
            [
                'name' => 'Sarah Johnson',
                'role' => 'Executive Director',
                'description' => 'Sarah has over 15 years of experience in nonprofit management and community development. She leads our organization with passion and dedication to making a positive impact in the community.',
                'email' => 'sarah@ngo-website.org',
                'phone' => '(555) 123-4567',
                'image' => '', // No image for sample data
                'status' => 'active',
                'display_order' => 1,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ],
            [
                'name' => 'Michael Chen',
                'role' => 'Program Manager',
                'description' => 'Michael oversees our community outreach programs and coordinates with local partners. His background in social work helps ensure our programs effectively serve those in need.',
                'email' => 'michael@ngo-website.org',
                'phone' => '(555) 123-4568',
                'image' => '', // No image for sample data
                'status' => 'active',
                'display_order' => 2,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ],
            [
                'name' => 'Emily Rodriguez',
                'role' => 'Volunteer Coordinator',
                'description' => 'Emily manages our volunteer programs and helps connect community members with meaningful opportunities to give back. She brings energy and enthusiasm to everything she does.',
                'email' => 'emily@ngo-website.org',
                'phone' => '(555) 123-4569',
                'image' => '', // No image for sample data
                'status' => 'active',
                'display_order' => 3,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];
        
        foreach ($sampleTeamMembers as $member) {
            $result = insertDocument($teamCollection, $member);
            if ($result && $result->getInsertedCount() > 0) {
                echo "✓ Created sample team member: '{$member['name']}'\n";
            } else {
                echo "✗ Failed to create sample team member: '{$member['name']}'\n";
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        echo "Error creating sample team members: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Insert sample activity data
 */
function insertSampleActivities() {
    echo "Creating sample activity data...\n";
    
    try {
        $activitiesCollection = getCollection('activities');
        if (!$activitiesCollection) {
            throw new Exception("Could not get activities collection");
        }
        
        // Check if activities already exist
        $existingCount = countDocuments($activitiesCollection);
        if ($existingCount > 0) {
            echo "✓ Activities already exist ($existingCount found)\n";
            return true;
        }
        
        $sampleActivities = [
            [
                'title' => 'Community Health Camp',
                'content' => 'We organized a free health camp in the local community, providing basic medical checkups, vaccinations, and health awareness sessions. Over 200 community members benefited from this initiative, receiving essential healthcare services that are often inaccessible due to financial constraints.',
                'image' => '', // No image for sample data
                'status' => 'published',
                'created_at' => new MongoDB\BSON\UTCDateTime(time() - 86400 * 7), // 7 days ago
                'updated_at' => new MongoDB\BSON\UTCDateTime(time() - 86400 * 7)
            ],
            [
                'title' => 'Educational Support Program',
                'content' => 'Our educational support program provided school supplies, books, and scholarships to underprivileged children in the area. This month, we were able to support 50 students with their educational needs, ensuring they can continue their studies without financial burden.',
                'image' => '', // No image for sample data
                'status' => 'published',
                'created_at' => new MongoDB\BSON\UTCDateTime(time() - 86400 * 14), // 14 days ago
                'updated_at' => new MongoDB\BSON\UTCDateTime(time() - 86400 * 14)
            ],
            [
                'title' => 'Food Distribution Drive',
                'content' => 'In response to the growing need in our community, we organized a food distribution drive that provided essential groceries and meals to 100 families. The drive was made possible through generous donations from local businesses and community members.',
                'image' => '', // No image for sample data
                'status' => 'published',
                'created_at' => new MongoDB\BSON\UTCDateTime(time() - 86400 * 21), // 21 days ago
                'updated_at' => new MongoDB\BSON\UTCDateTime(time() - 86400 * 21)
            ]
        ];
        
        foreach ($sampleActivities as $activity) {
            $result = insertDocument($activitiesCollection, $activity);
            if ($result && $result->getInsertedCount() > 0) {
                echo "✓ Created sample activity: '{$activity['title']}'\n";
            } else {
                echo "✗ Failed to create sample activity: '{$activity['title']}'\n";
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        echo "Error creating sample activities: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Test MongoDB connection
 */
function testConnection() {
    echo "Testing MongoDB connection...\n";
    
    if (testMongoConnection()) {
        echo "✓ MongoDB connection successful\n\n";
        return true;
    } else {
        echo "✗ MongoDB connection failed\n";
        echo "Please ensure MongoDB is running and the connection settings in config.php are correct.\n";
        return false;
    }
}

/**
 * Main initialization function
 */
function initializeDatabase() {
    echo "=== NGO Website Database Initialization ===\n\n";
    
    // Test connection first
    if (!testConnection()) {
        return false;
    }
    
    // Create indexes
    if (!createIndexes()) {
        return false;
    }
    
    // Insert default data
    if (!insertDefaultAdmin()) {
        return false;
    }
    
    if (!insertDefaultPages()) {
        return false;
    }
    
    if (!insertSampleActivities()) {
        return false;
    }
    
    if (!insertSampleTeamMembers()) {
        return false;
    }
    
    echo "\n=== Database initialization completed successfully! ===\n";
    echo "You can now access the admin panel at /admin/login.php\n";
    echo "Default credentials: username=admin, password=admin123\n";
    
    return true;
}

// Run initialization if script is called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    initializeDatabase();
}
?>