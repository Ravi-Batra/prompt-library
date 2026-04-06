<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATABASE CONNECTION
$host = 'localhost';
$db   = 'u465223560_promptdb';
$user = 'u465223560_promptdb';
$pass = 'Scraplead123';

$conn = new mysqli($host, $user, $pass, $db);

// 2. SAVE LOGIC
if (isset($_POST['add_prompt'])) {
    $category = $conn->real_escape_string($_POST['category']);
    $title = $conn->real_escape_string($_POST['title']);
    $text = $conn->real_escape_string($_POST['prompt_text']);
    $conn->query("INSERT INTO my_prompts (category, title, prompt_text) VALUES ('$category', '$title', '$text')");
}

// 3. DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM my_prompts WHERE id=$id");
    header("Location: index.php");
    exit;
}

// 4. FETCH DATA
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

$query = "SELECT * FROM my_prompts WHERE 1";

if ($search != '') {
    $query .= " AND (title LIKE '%$search%' OR category LIKE '%$search%' OR prompt_text LIKE '%$search%')";
}

if ($category != '') {
    $query .= " AND TRIM(LOWER(category)) = TRIM(LOWER('$category'))";
}

$query .= " ORDER BY id DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prompt Library</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { color: #1a73e8; text-align: center; margin-bottom: 30px; }

        .top-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .box { background: #fff; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; }

        input, textarea, select { width: 100%; margin-bottom: 12px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-save { background: #34a853; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; }

        #searchInput { border: 2px solid #1a73e8; font-size: 16px; }

        .tab-menu { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .tab-btn { padding: 10px 20px; cursor: pointer; border: none; background: #e8f0fe; color: #1a73e8; border-radius: 20px; font-weight: 500; }
        .tab-btn.active { background: #1a73e8; color: white; }

        .prompt-list { display: grid; grid-template-columns: 1fr; gap: 15px; }
        .card { background: white; border: 1px solid #e0e0e0; padding: 20px; border-radius: 10px; transition: 0.3s; }
        .card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-cat { font-size: 12px; color: #70757a; text-transform: uppercase; letter-spacing: 1px; }
        .card-title { font-size: 18px; font-weight: bold; margin: 8px 0; color: #202124; }
        .card-text { background: #f8f9fa; padding: 15px; border-radius: 8px; color: #3c4043; border: 1px dashed #dadce0; white-space: pre-wrap; margin-bottom: 15px; }

        .card-btns { display: flex; align-items: center; gap: 12px; border-top: 1px solid #eee; pt: 15px; padding-top: 15px; }
        .btn-copy { background: #1a73e8; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: 500; }
        .btn-edit { background: #5f6368; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-del { color: #d93025; text-decoration: none; font-size: 14px; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <h1>🚀 Prompt Library</h1>

    <div class="top-section">
        <div class="box">
            <h3>🔍 Quick Search</h3>
            <form method="GET">
    <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search by title, category, or text...">
</form>
            <p style="font-size: 12px; color: #666;">Searching across all categories instantly.</p>
        </div>

        <div class="box">
            <h3>➕ Add New</h3>
            <form method="POST">
                <select name="category" required>
                    <option value="Google Ads">Google Ads</option>
                    <option value="Voice AI">Voice AI</option>
                    <option value="Landing Page">Landing Page</option>
                    <option value="APPS">APPS</option>
                    <option value="Client Setup">Client Setup</option>
                </select>
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="prompt_text" placeholder="Prompt..." rows="3" required></textarea>
                <button type="submit" name="add_prompt" class="btn-save">Save</button>
            </form>
        </div>
    </div>

    <div class="tab-menu">
        <a href="?" class="tab-btn">All</a>
        <a href="?category=Google Ads" class="tab-btn">Google Ads</a>
        <a href="?category=Voice AI" class="tab-btn">Voice AI</a>
        <a href="?category=Landing Page" class="tab-btn">Landing Page</a>
        <a href="?category=APPS" class="tab-btn">APPS</a>
    </div>

    <div class="prompt-list" id="promptList">

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="card" data-category="<?php echo $row['category']; ?>">

                <span class="card-cat"><?php echo $row['category']; ?></span>

                <div class="card-title"><?php echo $row['title']; ?></div>

                <div class="card-text" id="p<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['prompt_text']); ?></div>

                <div class="card-btns">
                    <button class="btn-copy" onclick="copyText('p<?php echo $row['id']; ?>')">Copy Prompt</button>
                    
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                    
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete forever?')">Delete</a>
                </div>

            </div>

        <?php } ?>

    </div>
</div>

<script>

function filterCat(cat) {
    let cards = document.getElementsByClassName('card');
    let btns = document.getElementsByClassName('tab-btn');

    for (let btn of btns) btn.classList.remove('active');
    event.currentTarget.classList.add('active');

    for (let i = 0; i < cards.length; i++) {
        if (cat === 'all') {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = (cards[i].getAttribute('data-category') === cat) ? "" : "none";
        }
    }
}

function copyText(id) {
    var text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text);
    alert("Copied!");
}
</script>

</body>
</html>
