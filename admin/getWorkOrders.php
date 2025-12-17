<?php
include("includes/connection.php");

$contract_id = $_GET['contract_id'];

$sql = "
SELECT 
    wo.work_order_date,
    wo.work_order_number,
    s.id,
    s.site_name,
    st.service_name,
    wo.priority,
    wo.assigned_to,
    wo.logged_by,
    wo.value,
    wo.status
FROM recurring_work_orders wo
JOIN site_tbl s ON s.id = wo.site_id
JOIN service_type_tbl st ON st.service_id = wo.service_type_id
WHERE wo.contract_id = $contract_id
ORDER BY wo.work_order_date DESC
";

$res = $conn->query($sql);

while ($r = $res->fetch_assoc()) {
    echo "<tr>
        <td>".date('d/m/Y',strtotime($r['work_order_date']))."</td>
        <td>{$r['work_order_number']}</td>
        <td>{$r['id']}</td>
        <td>{$r['site_name']}</td>
        <td>{$r['service_name']}</td>
        <td>{$r['priority']}</td>
        <td>{$r['assigned_to']}</td>
        <td>{$r['logged_by']}</td>
        <td>$".number_format($r['value'],2)."</td>
        <td>{$r['status']}</td>
    </tr>";
}
