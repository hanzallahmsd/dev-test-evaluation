<?php
/**
 * Contact Controller
 * Handles contact form submissions
 */
namespace Controllers;

class ContactController
{
    /**
     * Database connection
     * @var \PDO
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = db();
    }
    
    /**
     * Send a contact message
     * 
     * @param array $data Message data
     * @return bool Whether the message was sent successfully
     */
    public function sendMessage(array $data): bool
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['subject']) || empty($data['message'])) {
            return false;
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        try {
            // Store the message in the database
            $stmt = $this->db->prepare(
                "INSERT INTO contact_messages (name, email, subject, message) 
                 VALUES (:name, :email, :subject, :message)"
            );
            
            $result = $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':subject' => $data['subject'],
                ':message' => $data['message']
            ]);
            
            if (!$result) {
                return false;
            }
            
            // In a production environment, you might also want to send an email notification
            // $to = "admin@example.com";
            // $subject = "New Contact Form Submission: " . $data['subject'];
            // $message = "Name: " . $data['name'] . "\n";
            // $message .= "Email: " . $data['email'] . "\n\n";
            // $message .= $data['message'];
            // $headers = "From: " . $data['email'];
            // mail($to, $subject, $message, $headers);
            
            return true;
        } catch (\Exception $e) {
            // Log the error
            error_log('Error saving contact message: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all contact messages
     * 
     * @param int $limit Limit the number of messages to return
     * @param int $offset Offset for pagination
     * @return array Array of messages
     */
    public function getMessages(int $limit = 10, int $offset = 0): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM contact_messages 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset"
            );
            
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error fetching contact messages: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update message status
     * 
     * @param int $id Message ID
     * @param string $status New status ('new', 'read', 'replied')
     * @return bool Whether the update was successful
     */
    public function updateStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE contact_messages 
                 SET status = :status 
                 WHERE id = :id"
            );
            
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch (\Exception $e) {
            error_log('Error updating message status: ' . $e->getMessage());
            return false;
        }
    }
}
