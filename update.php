<?php

require_once "functions.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

$pdo = new PDO('mysql:host=localhost; dbname=products_crud', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$statement = $pdo->prepare("SELECT * FROM products WHERE id= :id");
$statement->bindValue(':id', $id);
$statement->execute();
$product = $statement->fetch(PDO::FETCH_ASSOC);

$errors = [];

$title = $product['title'];
$description = $product['description'];
$price = $product['price'];
$imagePath = $product['image'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (!isset($product['image'])) {
        $imagePath = '';
    }
    $image = $_FILES['image'] ?? null;

    if (!is_dir('images')) {
        mkdir('images');
    }
    if ($image && $image['tmp_name']) {
        if ($product['image']) {
            unlink($product['image']);
        }
        $imagePath = 'images/' . randomString(8) . '/' . $image['name'];
        mkdir(dirname($imagePath));
        move_uploaded_file($image['tmp_name'], $imagePath);
    }
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];


    if (!$title) {
        $errors[] = 'Product Title is required';
    }
    if (!$price) {
        $errors[] = 'Product Price is required';
    }

    if (empty($errors)) {
        $statement = $pdo->prepare("UPDATE products SET  image = :image,
                title = :title,
                description = :description,
                price = :price WHERE id = :id");


        $statement->bindValue(':image', $imagePath);
        $statement->bindValue(':title', $title);
        $statement->bindValue(':description', $description);
        $statement->bindValue(':price', $price);
        $statement->bindValue(':id', $id);

        $statement->execute();
        header('Location: index.php');
    }
}


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link href="app.css" rel="stylesheet">
    <title>Products</title>
</head>
<body>
<p>
    <a href="index.php" class="btn btn-secondary">Back</a>
</p>

<h1>Update Product: <b><?php echo $product['title'] ?></b></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div>
                <?php echo $error ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <?php if ($product['image']): ?>
        <img src="<?php echo $product['image'] ?>" class="product-img-view">
    <?php endif; ?>
    <div class="form-group">
        <label>Product Image</label> <br>
        <input type="file" name="image">
    </div>
    <div class="form-group">
        <label>Product Title</label>
        <input type="text" name="title" class="form-control" value="<?php echo $title ?>">
    </div>
    <div class="form-group">
        <label>Product Description</label>
        <textarea name="description" class="form-control"><?php echo $description ?></textarea>
    </div>
    <div class="form-group">
        <label>Product Price</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $price ?>">
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
</form>

</body>
</html>