<?php

class Item {
    private $conn;
    
    public function __construct() {
        $host = "localhost";
        $db_name = "e_trieve";
        $username = "root";
        $password = "";
        
        $this->conn = new mysqli($host, $username, $password, $db_name);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    
  
    public function addItem($item_name, $category, $description, $estimated_value = null, $priority_point = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO items (item_name, category, description, estimated_value, priority_point) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $item_name, $category, $description, $estimated_value, $priority_point);
            
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            
            $stmt->close();
            return false;
            
        } catch (Exception $e) {
            error_log("Error adding item: " . $e->getMessage());
            return false;
        }
    }
    
   
    public function updateItem($item_id, $item_name, $category, $description, $estimated_value, $priority_point) {
        try {
            $stmt = $this->conn->prepare("UPDATE items SET item_name=?, category=?, description=?, estimated_value=?, priority_point=? WHERE item_id=?");
            $stmt->bind_param("ssssii", $item_name, $category, $description, $estimated_value, $priority_point, $item_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            
            $stmt->close();
            return false;
            
        } catch (Exception $e) {
            error_log("Error updating item: " . $e->getMessage());
            return false;
        }
    }
    
    
    public function deleteItem($item_id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM items WHERE item_id = ?");
            $stmt->bind_param("i", $item_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            
            $stmt->close();
            return false;
            
        } catch (Exception $e) {
            error_log("Error deleting item: " . $e->getMessage());
            return false;
        }
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

?>