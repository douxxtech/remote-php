<?php
/*
 ###                      #                ###   #  #  ###  
 #  #   ##   #  #   ##   ####   ##         #  #  #  #  #  # 
 ###   ####  ####  #  #   #    ####  ####  ###   ####  ###  
 # #   #     #  #  #  #   #    #           #     #  #  #    
 #  #   ##   #  #   ##     ##   ##         #     #  #  #  
------------------------------------------------------------
github.com/douxxu/remote-php | GPL-3.0 | Requiers a php server
*/

session_start();

$password = 'your_password_here';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['password']) || $input['password'] !== $password) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        exit;
    }

    if (!isset($_SESSION['active'])) {
        $_SESSION['active'] = true;
    }

    // get the request dir
    $currentDir = isset($input['currentDir']) ? $input['currentDir'] : getcwd();

    // if directory don't exist, give an error
    if (!is_dir($currentDir)) {
        echo json_encode(['status' => 'error', 'message' => "Invalid directory: $currentDir", 'currentDir' => $currentDir]);
        exit;
    }

    // change to the requested directory
    chdir($currentDir);

    $command = $input['command'];

    if ($command === 'exit') {
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Session ended']);
        exit;
    }

    // Handle the "cd" command
    if (preg_match('/^cd\s+(.*)$/', $command, $matches)) {
        $newDir = trim($matches[1]);
        // Check if the directory exists
        if (is_dir($newDir)) {
            chdir($newDir); // Change the directory
            $currentDir = getcwd();  // Update the current directory
            echo json_encode(['status' => 'success', 'output' => "Changed directory to $newDir", 'currentDir' => $currentDir]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Directory $newDir not found", 'currentDir' => $currentDir]);
        }
        exit;
    }

    // Execute commands
    ob_start();
    system(escapeshellcmd($command) . ' 2>&1', $return_var);
    $output = ob_get_clean();

    echo json_encode(['status' => 'success', 'output' => $output, 'currentDir' => getcwd()]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
