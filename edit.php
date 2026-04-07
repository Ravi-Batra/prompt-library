<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT * FROM my_prompts WHERE id=$id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "<h2 style='color:red;'>Error: Prompt not found</h2>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Prompt</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .container {
            width: 60%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }
        button {
            padding: 10px 20px;
            background: green;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Prompt</h2>

    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

        <input type="text" name="category" value="<?php echo $row['category']; ?>" placeholder="Category">

        <input type="text" name="title" value="<?php echo $row['title']; ?>" placeholder="Title">

        <textarea name="prompt_text" rows="6"><?php echo $row['prompt_text']; ?></textarea>

        <textarea name="role" placeholder="ROLE"><?php echo $row['role']; ?></textarea>

        <textarea name="task" placeholder="TASK"><?php echo $row['task']; ?></textarea>

        <textarea name="input" placeholder="INPUT"><?php echo $row['input']; ?></textarea>

        <textarea name="constraints" placeholder="CONSTRAINTS"><?php echo $row['constraints']; ?></textarea>

        <textarea name="output_format" placeholder="OUTPUT FORMAT"><?php echo $row['output_format']; ?></textarea>

        <input type="text" name="tags" value="<?php echo $row['tags']; ?>" placeholder="Tags (comma separated)">

        <button type="submit">Update Prompt</button>
    </form>
</div>

</body>
</html>
