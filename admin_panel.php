<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_supplier'])) {
        $name = $connection->real_escape_string($_POST['name']);
        $country = $connection->real_escape_string($_POST['country']);
        $contacts = $connection->real_escape_string($_POST['contacts']);
        
        $connection->query("INSERT INTO `Поставщики` SET 
            `Название` = '$name',
            `Страна` = '$country',
            `Контактная информация` = '$contacts'");
        header("Location: ?section=suppliers");
    }

    if (isset($_POST['update_supplier'])) {
        $id = (int)$_POST['id'];
        $name = $connection->real_escape_string($_POST['name']);
        $country = $connection->real_escape_string($_POST['country']);
        $contacts = $connection->real_escape_string($_POST['contacts']);
        
        $connection->query("UPDATE `Поставщики` SET 
            `Название` = '$name',
            `Страна` = '$country',
            `Контактная информация` = '$contacts'
            WHERE `ID-Поставщика` = $id");
		header("Location: ?section=suppliers");
    }

    if (isset($_POST['add_processor'])) {
        $model = $connection->real_escape_string($_POST['model']);
        $specs = $connection->real_escape_string($_POST['specs']);
        $price = (float)$_POST['price'];
        $date = $connection->real_escape_string($_POST['date']);
        $supplier = (int)$_POST['supplier'];
        $image = $connection->real_escape_string($_POST['image']);
        
        $connection->query("INSERT INTO `Процессоры` SET 
            `Модель` = '$model',
            `Характеристики` = '$specs',
            `Цена` = $price,
            `Дата Выпуска` = '$date',
            `image_url` = '$image',
            `ID-Поставщика` = $supplier");
        header("Location: ?section=processors");
    }

    if (isset($_POST['update_processor'])) {
        $id = (int)$_POST['id'];
        $model = $connection->real_escape_string($_POST['model']);
        $specs = $connection->real_escape_string($_POST['specs']);
        $price = (float)$_POST['price'];
        $date = $connection->real_escape_string($_POST['date']);
        $supplier = (int)$_POST['supplier'];
        $image = $connection->real_escape_string($_POST['image']);
        
        $connection->query("UPDATE `Процессоры` SET 
            `Модель` = '$model',
            `Характеристики` = '$specs',
            `Цена` = $price,
            `Дата Выпуска` = '$date',
            `image_url` = '$image',
            `ID-Поставщика` = $supplier
            WHERE `ID-Процессора` = $id");
		header("Location: ?section=processors");
    }

    if (isset($_POST['add_importer'])) {
        $location = $connection->real_escape_string($_POST['location']);
        $email = $connection->real_escape_string($_POST['email']);
        $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $image = $connection->real_escape_string($_POST['image']);
        
        $connection->query("INSERT INTO `Импортеры` SET 
            `Местоположение` = '$location',
            `email` = '$email',
            `pass` = '$pass',
            `image_url` = '$image'");
        header("Location: ?section=importers");
    }

    if (isset($_POST['update_importer'])) {
        $id = (int)$_POST['id'];
        $location = $connection->real_escape_string($_POST['location']);
        $email = $connection->real_escape_string($_POST['email']);
        $image = $connection->real_escape_string($_POST['image']);
        
        $query = "UPDATE `Импортеры` SET 
            `Местоположение` = '$location',
            `email` = '$email',
            `image_url` = '$image'";
        
        if (!empty($_POST['pass'])) {
            $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
            $query .= ", `pass` = '$pass'";
        }
        
        $query .= " WHERE `ID-Точки импорта` = $id";
        $connection->query($query);
		header("Location: ?section=importers");
    }

    if (isset($_POST['opw_add'])) {
        $proc = (int)$_POST['processor_id'];
        $wh   = (int)$_POST['warehouse_id'];
        $qty  = (int)$_POST['quantity'];

        $connection->query("INSERT INTO `Заказы` SET `Дата`=NOW(), `Статус`=0, `Тип`=1");
        $orderID = $connection->insert_id;

        $connection->query("INSERT INTO `Заказ_Процессор_Склад`
        (`ID-Заказа`,`ID-Процессора`,`ID-Склада`,`Количество`)
        VALUES ($orderID,$proc,$wh,$qty)");
        header("Location: ?section=opw"); exit;
    }

    if (isset($_POST['opw_move'])) {
        [$oldOrder,$oldProc,$oldWh] = array_map('intval', explode('-', $_POST['row_key']));
        $newWh  = (int)$_POST['new_warehouse'];

        $qty = (int)$_POST['old_qty'];
        $stmt = $connection->prepare("
            INSERT INTO `Заказ_Процессор_Склад`
            (`ID-Заказа`,`ID-Процессора`,`ID-Склада`,`Количество`)
            VALUES (?,?,?,?)");
        $stmt->bind_param("iiii", $oldOrder, $oldProc, $newWh, $qty);
        $stmt->execute();   $stmt->close();

        $connection->query("
            DELETE FROM `Заказ_Процессор_Склад`
            WHERE `ID-Заказа`=$oldOrder AND `ID-Процессора`=$oldProc AND `ID-Склада`=$oldWh
            LIMIT 1");

        header("Location: ?section=opw");  exit;
    }
    if (isset($_POST['opw_move_group'])) {
        $proc   = (int)$_POST['proc_id'];
        $oldWh  = (int)$_POST['old_wh'];
        $newWh  = (int)$_POST['new_wh'];
        $qty    = (int)$_POST['qty'];

        if ($qty < 1) {
            die("Неверное количество.");
        }

        $res = $connection->query("
        SELECT SUM(`Количество`) as total
        FROM `Заказ_Процессор_Склад`
        WHERE `ID-Процессора` = $proc AND `ID-Склада` = $oldWh
        ");
        $row = $res->fetch_assoc();
        $totalAvailable = (int)$row['total'];

        if ($qty > $totalAvailable) {
            die("Нельзя переместить больше, чем есть на складе.");
        }

        $connection->query("INSERT INTO `Заказы` SET `Дата`=NOW(), `Статус`=0, `Тип`=1");
        $orderID = $connection->insert_id;

        $connection->query("
        INSERT INTO `Заказ_Процессор_Склад`
        (`ID-Заказа`, `ID-Процессора`, `ID-Склада`, `Количество`)
        VALUES ($orderID, $proc, $newWh, $qty)
        ");

        $res = $connection->query("
        SELECT `ID-Заказа`, `Количество`
        FROM `Заказ_Процессор_Склад`
        WHERE `ID-Процессора` = $proc AND `ID-Склада` = $oldWh
        ORDER BY `ID-Заказа` ASC
        ");

        $toSubtract = $qty;
        while ($row = $res->fetch_assoc()) {
            $zid = $row['ID-Заказа'];
            $cnt = (int)$row['Количество'];

            if ($toSubtract <= 0) break;

            if ($cnt <= $toSubtract) {
                $connection->query("
                DELETE FROM `Заказ_Процессор_Склад`
                WHERE `ID-Процессора` = $proc AND `ID-Склада` = $oldWh AND `ID-Заказа` = $zid
                LIMIT 1
                ");
                $toSubtract -= $cnt;
            } else {
                $left = $cnt - $toSubtract;
                $connection->query("
                UPDATE `Заказ_Процессор_Склад`
                SET `Количество` = $left
                WHERE `ID-Процессора` = $proc AND `ID-Склада` = $oldWh AND `ID-Заказа` = $zid
                LIMIT 1
                ");
                $toSubtract = 0;
            }
        }

        header("Location: ?section=opw"); exit;
    }



    if (isset($_POST['add_warehouse'])) {
        $area = (int)$_POST['area'];
        $location = $connection->real_escape_string($_POST['location']);
        $status = (int)$_POST['status'];
        $importer_id = (int)$_POST['importer_id'];
        
        $connection->query("INSERT INTO `Склады` SET 
            `Площадь` = $area,
            `Местоположение` = '$location',
            `Статус` = $status,
            `ID-Точки импорта` = $importer_id");
        header("Location: ?section=warehouses");
    }

    if (isset($_POST['update_warehouse'])) {
        $id = (int)$_POST['id'];
        $area = (int)$_POST['area'];
        $location = $connection->real_escape_string($_POST['location']);
        $status = (int)$_POST['status'];
        $importer_id = (int)$_POST['importer_id'];
        
        $connection->query("UPDATE `Склады` SET 
            `Площадь` = $area,
            `Местоположение` = '$location',
            `Статус` = $status,
            `ID-Точки импорта` = $importer_id
            WHERE `ID-Склада` = $id");
		header("Location: ?section=warehouses");
    }
	if (isset($_POST['update_item'])) {
    $order_id = (int)$_POST['order_id'];
    list($old_processor, $old_warehouse) = explode('-', $_POST['item_id']);
    
    $new_processor = (int)$_POST['processor'];
    $new_warehouse = (int)$_POST['warehouse'];
    $quantity = (int)$_POST['quantity'];
    
    $connection->query("UPDATE `Заказ_Процессор_Склад` 
        SET `ID-Процессора` = $new_processor,
            `ID-Склада` = $new_warehouse,
            `Количество` = $quantity
        WHERE `ID-Заказа` = $order_id 
        AND `ID-Процессора` = ".(int)$old_processor."
        AND `ID-Склада` = ".(int)$old_warehouse);
    
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

	if (isset($_POST['update_status'])) {
		$order_id = (int)$_POST['order_id'];
		$status = (int)$_POST['status'];
		$connection->query("UPDATE `Заказы` SET `Статус` = $status WHERE `ID-Заказа` = $order_id");

		header("Location: ?section=orders&importer_id=" . $selected_importer);
		exit();
	}

	if (isset($_POST['update_order_items'])) {
		$order_id = (int)$_POST['order_id'];

		if (isset($_POST['remove_items'])) {
			foreach ($_POST['remove_items'] as $item_id) {
				list($processor_id, $warehouse_id) = explode('-', $item_id);
				$connection->query("DELETE FROM `Заказ_Процессор_Склад` 
					WHERE `ID-Заказа` = $order_id 
					  AND `ID-Процессора` = ".(int)$processor_id."
					  AND `ID-Склада` = ".(int)$warehouse_id);
			}
		}

		if (isset($_POST['item'])) {
			foreach ($_POST['item'] as $item_id => $quantity) {
				list($processor_id, $warehouse_id) = explode('-', $item_id);
				$quantity = (int)$quantity;
				if ($quantity > 0) {
					$connection->query("UPDATE `Заказ_Процессор_Склад` 
						SET `Количество` = $quantity 
						WHERE `ID-Заказа` = $order_id 
						  AND `ID-Процессора` = ".(int)$processor_id."
						  AND `ID-Склада` = ".(int)$warehouse_id);
				}
			}
		}

		header("Location: ?section=orders&importer_id=" . $selected_importer);
		exit();
	}
}

if (isset($_GET['delete'])) {
    $table = $_GET['table'];
    $id = (int)$_GET['id'];
    
    $allowed_tables = [
        'Поставщики' => 'ID-Поставщика',
        'Процессоры' => 'ID-Процессора',
        'Заказы' => 'ID-Заказа',
        'Импортеры' => 'ID-Точки импорта',
        'Склады' => 'ID-Склада'
    ];
    
    if (array_key_exists($table, $allowed_tables)) {
        $primaryKey = $allowed_tables[$table];
        $connection->query("DELETE FROM `$table` WHERE `$primaryKey` = $id");
    }
}

    if (isset($_GET['delete']) && $_GET['table']==='Заказ_Процессор_Склад') {
        $proc = (int)$_GET['proc_id'];
        $wh   = (int)$_GET['wh_id'];

        $connection->query("
        DELETE FROM `Заказ_Процессор_Склад`
        WHERE `ID-Процессора`=$proc AND `ID-Склада`=$wh
        ");
        header("Location: ?section=opw"); exit;
    }

$section = $_GET['section'] ?? 'orders';
$selected_importer = $_GET['importer_id'] ?? null;

$suppliers = $connection->query("SELECT * FROM `Поставщики`");
$processors = $connection->query("SELECT * FROM `Процессоры`");
$importers = $connection->query("SELECT * FROM `Импортеры`");
$warehouses = $connection->query("
    SELECT s.*, i.`Местоположение` as importer_location 
    FROM `Склады` s 
    JOIN `Импортеры` i ON s.`ID-Точки импорта` = i.`ID-Точки импорта`
");

if ($selected_importer) {
    $orders = $connection->query("
        SELECT z.* 
        FROM `Заказы` z
        JOIN `Заказы на импорт` zi ON z.`ID-Заказа` = zi.`ID-Заказа`
        WHERE zi.`ID-Точки импорта` = $selected_importer
    ");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Административная панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-link.active { font-weight: 700; }
        .preview-image { max-width: 150px; }
        .status-0 { background: #fff3cd; }
        .status-1 { background: #d4edda; }
        .status-2 { background: #f8d7da; }
		.modal-content {
			padding: 20px;
		}
		.modal-header {
			border-bottom: 1px solid #dee2e6;
			padding-bottom: 15px;
		}
		.modal-footer {
			border-top: 1px solid #dee2e6;
			padding-top: 15px;
		}
        .table {
            table-layout: auto;
        }

        .table td:last-child {
            width: 1%;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Админ-панель</span>
            <div class="navbar-nav flex-row gap-3">
                <a href="/" class="text-white">Клиентская часть</a>
                <a href="logout.php" class="text-danger">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="nav-section mb-4">
            <div class="btn-group">
                <a href="?section=orders" class="btn btn-outline-primary <?= $section === 'orders' ? 'active' : '' ?>">Заказы</a>
                <a href="?section=suppliers" class="btn btn-outline-primary <?= $section === 'suppliers' ? 'active' : '' ?>">Поставщики</a>
                <a href="?section=processors" class="btn btn-outline-primary <?= $section === 'processors' ? 'active' : '' ?>">Процессоры</a>
                <a href="?section=importers" class="btn btn-outline-primary <?= $section === 'importers' ? 'active' : '' ?>">Импортеры</a>
                <a href="?section=warehouses" class="btn btn-outline-primary <?= $section === 'warehouses' ? 'active' : '' ?>">Склады</a>
                <a href="?section=opw" class="btn btn-outline-primary <?= $section === 'opw' ? 'active' : '' ?>">Наличие</a>
            </div>
        </div>

		<?php if ($section === 'orders'): ?>
		<div class="card">
			<div class="card-header">Управление заказами</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4">
						<h5>Импортеры</h5>
						<div class="list-group">
							<?php 
							$importers_list = $connection->query("SELECT * FROM `Импортеры`");
							while ($importer = $importers_list->fetch_assoc()): ?>
								<a href="?section=orders&importer_id=<?= $importer['ID-Точки импорта'] ?>" 
								   class="list-group-item <?= $selected_importer == $importer['ID-Точки импорта'] ? 'active' : '' ?>">
									<?= htmlspecialchars($importer['Местоположение']) ?>
								</a>
							<?php endwhile; ?>      
						</div>
					</div>

					<div class="col-md-8">
						<?php if ($selected_importer): ?>
							<h5>Заказы импортера</h5>
							<div class="accordion" id="ordersAccordion">
								<?php 
								$orders = $connection->query("
									SELECT z.* 
									FROM `Заказы` z
									JOIN `Заказы на импорт` zi ON z.`ID-Заказа` = zi.`ID-Заказа`
									WHERE zi.`ID-Точки импорта` = $selected_importer
									ORDER BY z.`Дата` DESC
								");
								
								while ($order = $orders->fetch_assoc()):
									$isEditing = (isset($_GET['edit']) && (int)$_GET['edit'] === (int)$order['ID-Заказа']);

									$order_details = $connection->query("
										SELECT zps.*, p.`Модель`, s.`Местоположение` AS склад 
										FROM `Заказ_Процессор_Склад` zps
										JOIN `Процессоры` p ON zps.`ID-Процессора` = p.`ID-Процессора`
										JOIN `Склады` s ON zps.`ID-Склада` = s.`ID-Склада`
										WHERE zps.`ID-Заказа` = {$order['ID-Заказа']}
									");
								?>
									<div class="accordion-item">
										<h2 class="accordion-header" id="heading<?= $order['ID-Заказа'] ?>">
											<button 
												class="accordion-button <?= $isEditing ? '' : 'collapsed' ?>" 
												type="button" 
												data-bs-toggle="collapse" 
												data-bs-target="#collapse<?= $order['ID-Заказа'] ?>" 
												aria-expanded="<?= $isEditing ? 'true' : 'false' ?>" 
												aria-controls="collapse<?= $order['ID-Заказа'] ?>">
												Заказ #<?= $order['ID-Заказа'] ?> от <?= date('d.m.Y', strtotime($order['Дата'])) ?>
											</button>
										</h2>

										<div 
											id="collapse<?= $order['ID-Заказа'] ?>" 
											class="accordion-collapse collapse <?= $isEditing ? 'show' : '' ?>" 
											aria-labelledby="heading<?= $order['ID-Заказа'] ?>" 
											data-bs-parent="#ordersAccordion">
											<div class="accordion-body">
												<div class="d-flex align-items-center mb-2">
                                                    <label class="form-label me-2 mb-0">Статус заказа:</label>
                                                    <?php if (!$isEditing): ?>
                                                        <p class="form-control-plaintext mb-0">
                                                            <?= ((int)$order['Статус'] === 0) ? 'Новый' : 'Доставлен' ?>
                                                        </p>
                                                    <?php else: ?>
                                                        <form method="post" class="d-flex align-items-center">
                                                            <input type="hidden" name="order_id" value="<?= $order['ID-Заказа'] ?>">
                                                            <select name="status" class="form-select w-auto me-2">
                                                                <option value="0" <?= $order['Статус'] == 0 ? 'selected' : '' ?>>Новый</option>
                                                                <option value="1" <?= $order['Статус'] == 1 ? 'selected' : '' ?>>Доставлен</option>
                                                            </select>
                                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary me-2">Сохранить</button>
                                                            <input type="hidden" name="redirect_no_edit" value="1">
                                                        </form>
                                                    <?php endif; ?>
                                                </div>

												<hr>

												<div class="table-responsive">
													<table class="table table-sm">
														<thead>
															<tr>
																<th>Процессор</th>
																<th>Склад</th>
																<th>Количество</th>
																<th>Действия</th>
															</tr>
														</thead>
														<tbody>
															<?php while ($detail = $order_details->fetch_assoc()): ?>
																<tr>
																	<?php if (!$isEditing): ?>
																		<td><?= htmlspecialchars($detail['Модель']) ?></td>
																		<td><?= htmlspecialchars($detail['склад']) ?></td>
																		<td><?= (int)$detail['Количество'] ?></td>
																		<td>
																			<span class="text-muted">—</span>
																		</td>
																	<?php else: ?>
																		<form method="post">
																			<input type="hidden" name="order_id" value="<?= $order['ID-Заказа'] ?>">
																			<input type="hidden" 
																				   name="item_id" 
																				   value="<?= $detail['ID-Процессора'] ?>-<?= $detail['ID-Склада'] ?>">
																			<td>
																				<select name="processor" class="form-select form-select-sm">
																					<?php
																						$allProcs = $connection->query("SELECT `ID-Процессора`, `Модель` FROM `Процессоры`");
																						while ($p = $allProcs->fetch_assoc()):
																					?>
																						<option 
																							value="<?= $p['ID-Процессора'] ?>"
																							<?= $p['ID-Процессора'] == $detail['ID-Процессора'] ? 'selected' : '' ?>>
																							<?= htmlspecialchars($p['Модель']) ?>
																						</option>
																					<?php endwhile; ?>
																				</select>
																			</td>
																			<td>
																				<select name="warehouse" class="form-select form-select-sm">
																					<?php
																						$allWarehouses = $connection->query("SELECT `ID-Склада`, `Местоположение` FROM `Склады`");
																						while ($w = $allWarehouses->fetch_assoc()):
																					?>
																						<option 
																							value="<?= $w['ID-Склада'] ?>"
																							<?= $w['ID-Склада'] == $detail['ID-Склада'] ? 'selected' : '' ?>>
																							<?= htmlspecialchars($w['Местоположение']) ?>
																						</option>
																					<?php endwhile; ?>
																				</select>
																			</td>
																			<td>
																				<input type="number" 
																					   name="quantity" 
																					   value="<?= (int)$detail['Количество'] ?>" 
																					   class="form-control form-control-sm" 
																					   min="1">
																			</td>
																			<td>
																				<div class="btn-group btn-group-sm" role="group">
																					<button type="submit" 
																							name="update_item" 
																							class="btn btn-success"
																							onclick="return confirm('Сохранить изменения в этой строке?')">
																						Сохранить
																					</button>
																					<button type="submit" 
																							name="remove_items[]" 
																							value="<?= $detail['ID-Процессора'] ?>-<?= $detail['ID-Склада'] ?>" 
																							class="btn btn-danger"
																							onclick="return confirm('Удалить эту позицию?')">
																						Удалить
																					</button>
																				</div>
																			</td>
																		</form>
																	<?php endif; ?>
																</tr>
															<?php endwhile; ?>
														</tbody>
													</table>
												</div>

												<hr>

												<div class="d-flex justify-content-between align-items-center">
													<?php if (!$isEditing): ?>
														<a href="?section=orders&importer_id=<?= $selected_importer ?>&edit=<?= $order['ID-Заказа'] ?>" 
														   class="btn btn-secondary">
															Редактировать
														</a>
													<?php else: ?>
														<a href="?section=orders&importer_id=<?= $selected_importer ?>" 
														   class="btn btn-outline-secondary">
															Закрыть редактирование
														</a>
													<?php endif; ?>

													<a href="?delete&table=Заказы&id=<?= $order['ID-Заказа'] ?>" 
													   class="btn btn-danger"
													   onclick="return confirm('Удалить весь заказ?')">
														Удалить заказ
													</a>
												</div>
											</div> 
										</div> 
									</div> 
								<?php endwhile; ?>
							</div> 
						<?php else: ?>
							<div class="alert alert-info">Выберите импортера для просмотра заказов</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
        <?php if ($section === 'suppliers'): ?>
        <div class="card">
            <div class="card-header">Управление поставщиками</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>Список поставщиков</h5>
                        <table class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Страна</th>
                                    <th>Контакты</th>
                                    <th style="width: 180px;">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                                    <tr id="row-<?= $supplier['ID-Поставщика'] ?>">
                                        <form method="post">
                                            <input type="hidden" name="id" value="<?= $supplier['ID-Поставщика'] ?>">
                                            <td class="view-mode"><?= htmlspecialchars($supplier['Название']) ?></td>
                                            <td class="view-mode"><?= htmlspecialchars($supplier['Страна']) ?></td>
                                            <td class="view-mode"><?= htmlspecialchars($supplier['Контактная информация']) ?></td>

                                            <td class="edit-mode d-none">
                                                <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($supplier['Название']) ?>" required>
                                            </td>
                                            <td class="edit-mode d-none">
                                                <input type="text" name="country" class="form-control form-control-sm" value="<?= htmlspecialchars($supplier['Страна']) ?>" required>
                                            </td>
                                            <td class="edit-mode d-none">
                                                <input type="text" name="contacts" class="form-control form-control-sm" value="<?= htmlspecialchars($supplier['Контактная информация']) ?>" required>
                                            </td>

                                            <td>
                                                <div class="btn-group view-mode">
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="enableEdit(<?= $supplier['ID-Поставщика'] ?>)">Редактировать</button>
                                                    <a href="?delete&table=Поставщики&id=<?= $supplier['ID-Поставщика'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить поставщика?')">Удалить</a>
                                                </div>

                                                <div class="btn-group edit-mode d-none">
                                                    <button type="submit" name="update_supplier" class="btn btn-sm btn-primary">Сохранить</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit(<?= $supplier['ID-Поставщика'] ?>)">Отмена</button>
                                                </div>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                            Добавить поставщика
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="post" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSupplierModalLabel">Добавить поставщика</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Название</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Страна</label>
                                    <input type="text" name="country" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Контакты</label>
                                    <input type="text" name="contacts" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="add_supplier" class="btn btn-success">Добавить</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($section === 'processors'): ?>
            <div class="card">
                <div class="card-header">Управление процессорами</div>
                <div class="card-body">
                    <?php if (isset($_GET['edit'])): 
                        $processor = $connection->query("SELECT * FROM `Процессоры` WHERE `ID-Процессора` = ".(int)$_GET['edit'])->fetch_assoc();
                        $suppliers_list = $connection->query("SELECT * FROM `Поставщики`");
                    ?>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $processor['ID-Процессора'] ?>">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="model" class="form-control" 
                                           value="<?= htmlspecialchars($processor['Модель']) ?>" placeholder="Модель" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="price" class="form-control" 
                                           value="<?= $processor['Цена'] ?>" step="0.01" placeholder="Цена" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" name="date" class="form-control" 
                                           value="<?= $processor['Дата Выпуска'] ?>" required>
                                </div>
                                <div class="col-12">
                                    <textarea name="specs" class="form-control" 
                                              placeholder="Характеристики" required><?= htmlspecialchars($processor['Характеристики']) ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="image" class="form-control" 
                                           value="<?= htmlspecialchars($processor['image_url']) ?>" placeholder="URL изображения">
                                </div>
                                <div class="col-md-6">
                                    <select name="supplier" class="form-select" required>
                                        <?php while ($supplier = $suppliers_list->fetch_assoc()): ?>
                                            <option value="<?= $supplier['ID-Поставщика'] ?>" 
                                                <?= $supplier['ID-Поставщика'] == $processor['ID-Поставщика'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($supplier['Название']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="update_processor" class="btn btn-primary">Сохранить</button>
                                    <a href="?section=processors" class="btn btn-secondary">Отмена</a>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Список процессоров</h5>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Модель</th>
                                            <th>Цена</th>
                                            <th>Изображение</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($processor = $processors->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($processor['Модель']) ?></td>
                                                <td><?= number_format($processor['Цена'], 0, ',', ' ') ?> ₽</td>
                                                <td>
                                                    <?php if ($processor['image_url']): ?>
                                                        <img src="<?= $processor['image_url'] ?>" class="preview-image">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="?section=processors&edit=<?= $processor['ID-Процессора'] ?>" 
                                                    class="btn btn-sm btn-warning">Редактировать️</a>
                                                    <a href="?delete&table=Процессоры&id=<?= $processor['ID-Процессора'] ?>" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Удалить процессор?')">Удалить️</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>

                                <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addProcessorModal">
                                    Добавить процессор
                                </button>
                            </div>
                        </div>

                        <div class="modal fade" id="addProcessorModal" tabindex="-1" aria-labelledby="addProcessorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form method="post" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addProcessorModalLabel">Добавить процессор</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" name="model" class="form-control" placeholder="Модель" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" name="price" class="form-control" step="0.01" placeholder="Цена" required>
                                    </div>
                                    <div class="col-md-12">
                                        <textarea name="specs" class="form-control" placeholder="Характеристики" required></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" name="date" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="image" class="form-control" placeholder="URL изображения">
                                    </div>
                                    <div class="col-md-12">
                                        <select name="supplier" class="form-select" required>
                                            <?php
                                            $suppliers_list = $connection->query("SELECT * FROM `Поставщики`");
                                            while ($supplier = $suppliers_list->fetch_assoc()):
                                            ?>
                                                <option value="<?= $supplier['ID-Поставщика'] ?>">
                                                    <?= htmlspecialchars($supplier['Название']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="add_processor" class="btn btn-success">Добавить</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            </div>
                            </form>
                        </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>

        <?php if ($section === 'importers'): ?>
        <div class="card">
            <div class="card-header">Управление импортерами</div>
            <div class="card-body">
                <?php if (isset($_GET['edit'])): 
                    $importer = $connection->query("SELECT * FROM `Импортеры` WHERE `ID-Точки импорта` = ".(int)$_GET['edit'])->fetch_assoc();
                ?>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $importer['ID-Точки импорта'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="location" class="form-control" 
                                    value="<?= htmlspecialchars($importer['Местоположение']) ?>" placeholder="Местоположение" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" 
                                    value="<?= htmlspecialchars($importer['email']) ?>" placeholder="Email" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="pass" class="form-control" placeholder="Новый пароль">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="image" class="form-control" 
                                    value="<?= htmlspecialchars($importer['image_url']) ?>" placeholder="URL изображения">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_importer" class="btn btn-primary">Сохранить</button>
                                <a href="?section=importers" class="btn btn-secondary">Отмена</a>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Список импортеров</h5>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Местоположение</th>
                                        <th>Email</th>
                                        <th>Изображение</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($importer = $importers->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($importer['Местоположение']) ?></td>
                                            <td><?= htmlspecialchars($importer['email']) ?></td>
                                            <td>
                                                <?php if ($importer['image_url']): ?>
                                                    <img src="<?= $importer['image_url'] ?>" class="preview-image">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?section=importers&edit=<?= $importer['ID-Точки импорта'] ?>" 
                                                class="btn btn-sm btn-warning">Редактировать️</a>
                                                <a href="?delete&table=Импортеры&id=<?= $importer['ID-Точки импорта'] ?>" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Удалить импортера?')">Удалить️</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addImporterModal">
                                Добавить импортера
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="addImporterModal" tabindex="-1" aria-labelledby="addImporterModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addImporterModalLabel">Добавить импортера</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="text" name="location" class="form-control" placeholder="Местоположение" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" name="pass" class="form-control" placeholder="Пароль" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="image" class="form-control" placeholder="URL изображения">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="add_importer" class="btn btn-success">Добавить</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

        <?php if ($section === 'warehouses'): ?>
        <div class="card">
            <div class="card-header">Управление складами</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>Список складов</h5>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Площадь</th>
                                    <th>Адрес</th>
                                    <th>Статус</th>
                                    <th>Импортер</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($warehouse = $warehouses->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $warehouse['Площадь'] ?> м²</td>
                                        <td><?= htmlspecialchars($warehouse['Местоположение']) ?></td>
                                        <td><?= $warehouse['Статус'] ? 'Активен' : 'Неактивен' ?></td>
                                        <td><?= htmlspecialchars($warehouse['importer_location']) ?></td>
                                        <td>
                                            <a href="?section=warehouses&edit=<?= $warehouse['ID-Склада'] ?>" 
                                            class="btn btn-sm btn-warning">Редактировать️</a>
                                            <a href="?delete&table=Склады&id=<?= $warehouse['ID-Склада'] ?>" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Удалить склад?')">Удалить️</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                            Добавить склад
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="addWarehouseModal" tabindex="-1" aria-labelledby="addWarehouseModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form method="post" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addWarehouseModalLabel">Добавить склад</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="number" name="area" class="form-control" placeholder="Площадь" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="location" class="form-control" placeholder="Адрес" required>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="status" class="form-select" required>
                                            <option value="0">Неактивен</option>
                                            <option value="1">Активен</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="importer_id" class="form-select" required>
                                            <?php
                                            $importers_list = $connection->query("SELECT * FROM `Импортеры`");
                                            while ($importer = $importers_list->fetch_assoc()):
                                            ?>
                                                <option value="<?= $importer['ID-Точки импорта'] ?>">
                                                    <?= htmlspecialchars($importer['Местоположение']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="add_warehouse" class="btn btn-success">Добавить</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            </div>
                        </form>
                    </div>
                </div>


                <?php if (isset($_GET['edit'])): 
                    $warehouse = $connection->query("SELECT * FROM `Склады` WHERE `ID-Склада` = ".(int)$_GET['edit'])->fetch_assoc();
                    $importers_list = $connection->query("SELECT * FROM `Импортеры`");
                ?>
                    <hr class="my-4">
                    <h5>Редактирование склада</h5>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $warehouse['ID-Склада'] ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="number" name="area" class="form-control" 
                                    value="<?= $warehouse['Площадь'] ?>" placeholder="Площадь" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="location" class="form-control" 
                                    value="<?= htmlspecialchars($warehouse['Местоположение']) ?>" placeholder="Адрес" required>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select" required>
                                    <option value="0" <?= $warehouse['Статус'] == 0 ? 'selected' : '' ?>>Неактивен</option>
                                    <option value="1" <?= $warehouse['Статус'] == 1 ? 'selected' : '' ?>>Активен</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <select name="importer_id" class="form-select" required>
                                    <?php while ($importer = $importers_list->fetch_assoc()): ?>
                                        <option value="<?= $importer['ID-Точки импорта'] ?>" 
                                            <?= $importer['ID-Точки импорта'] == $warehouse['ID-Точки импорта'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($importer['Местоположение']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_warehouse" class="btn btn-primary">Сохранить</button>
                                <a href="?section=warehouses" class="btn btn-secondary">Отмена</a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($section === 'opw'): ?>
    <div class="card">
    <div class="card-header">Наличие</div>
    <div class="card-body">

    <?php
    $rows = $connection->query("
    SELECT p.`ID-Процессора` proc_id, p.`Модель` proc_name,
            s.`ID-Склада` wh_id,  s.`Местоположение` wh_name,
            SUM(zpw.`Количество`) qty
    FROM `Заказ_Процессор_Склад` zpw
    JOIN `Процессоры` p ON p.`ID-Процессора` = zpw.`ID-Процессора`
    JOIN `Склады`      s ON s.`ID-Склада`     = zpw.`ID-Склада`
    GROUP BY p.`ID-Процессора`, s.`ID-Склада`
    ORDER BY p.`Модель`, s.`Местоположение`
    ");

    $data = [];
    while ($r = $rows->fetch_assoc()) {
        $pid = $r['proc_id'];
        $data[$pid]['name']             = $r['proc_name'];
        $data[$pid]['warehouses'][]     = [
            'wh_id' => $r['wh_id'],
            'wh_name' => $r['wh_name'],
            'qty'     => $r['qty']
        ];
    }
    ?>

    <div class="accordion" id="procAcc">
    <?php foreach ($data as $pid => $item): ?>
    <div class="accordion-item">
        <h2 class="accordion-header" id="h<?=$pid?>">
        <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#c<?=$pid?>">
            <?= htmlspecialchars($item['name']) ?>
        </button>
        </h2>
        <div id="c<?=$pid?>" class="accordion-collapse collapse" data-bs-parent="#procAcc">
        <div class="accordion-body p-0">
            <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                <th>Склад</th>
                <th class="text-end">Кол-во</th>
                <th style="width:210px">Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($item['warehouses'] as $w): 
                    $key = $pid.'-'.$w['wh_id']; ?>
                <tr>
                <td><?= htmlspecialchars($w['wh_name']) ?></td>
                <td class="text-end"><?= $w['qty'] ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="collapse"
                            data-bs-target="#move<?=$key?>">Переместить</button>
                    <a class="btn btn-danger btn-sm"
                    href="?section=opw&delete=1&table=Заказ_Процессор_Склад&proc_id=<?=$pid?>&wh_id=<?=$w['wh_id']?>"
                    onclick="return confirm('Удалить все записи этого процессора на складе?')">Удалить</a>
                </td>
                </tr>

                <tr class="collapse" id="move<?=$key?>">
                <td colspan="3">
                    <form method="post" class="d-flex gap-2 align-items-end flex-wrap">
                    <input type="hidden" name="proc_id"  value="<?=$pid?>">
                    <input type="hidden" name="old_wh"   value="<?=$w['wh_id']?>">
                    <label class="form-label mb-0">Сколько:</label>
                    <input type="number" name="qty" class="form-control w-auto" min="1" max="<?=$w['qty']?>" required>

    <?php
    $whList = $connection->query("SELECT `ID-Склада`,`Местоположение` FROM `Склады`");
    ?>
                    <select name="new_wh" class="form-select w-auto" required>
                        <?php while($row=$whList->fetch_assoc()): ?>
                        <option value="<?=$row['ID-Склада']?>"
                            <?=$row['ID-Склада']==$w['wh_id']?'disabled':''?>>
                            <?=htmlspecialchars($row['Местоположение'])?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" name="opw_move_group" class="btn btn-success btn-sm">
                        Перенести
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-target="#move<?=$key?>" data-bs-toggle="collapse">
                        Отмена
                    </button>
                    </form>
                </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
        </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addOPW">
    Добавить запись
    </button>

    </div></div>

    <div class="modal fade" id="addOPW" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <form method="post" class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Добавить процессор на склад</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Процессор</label>
            <select name="processor_id" class="form-select" required>
            <?php $pList=$connection->query("SELECT `ID-Процессора`,`Модель` FROM `Процессоры`");
            while($p=$pList->fetch_assoc()): ?>
                <option value="<?=$p['ID-Процессора']?>"><?=htmlspecialchars($p['Модель'])?></option>
            <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Склад</label>
            <select name="warehouse_id" class="form-select" required>
            <?php $wList=$connection->query("SELECT `ID-Склада`,`Местоположение` FROM `Склады`");
            while($w=$wList->fetch_assoc()): ?>
                <option value="<?=$w['ID-Склада']?>"><?=htmlspecialchars($w['Местоположение'])?></option>
            <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Количество</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
        </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="opw_add" class="btn btn-success">Добавить</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
    </div>
    </form>
    </div>
    </div>
    <?php endif; ?>



    </div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
function enableEdit(id) {
    const row = document.getElementById('row-' + id);
    row.querySelectorAll('.view-mode').forEach(el => el.classList.add('d-none'));
    row.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('d-none'));
}

function cancelEdit(id) {
    const row = document.getElementById('row-' + id);
    row.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
    row.querySelectorAll('.view-mode').forEach(el => el.classList.remove('d-none'));
}
</script>

</body>

</html>
