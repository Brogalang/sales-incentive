<?php $no = 1;
foreach ($si as $key => $value) : ?>
    <tr>
        <td><?= $no ?></td>
        <td><?= $value['BILLING_NO'] ?></td>
        <td><?= $value['BILLING_ITEM'] ?></td>
        <td><?= $value['BILLING_TYP'] ?></td>
        <td><?= $value['CHAR_SIZE'] ?></td>
        <td><?= $value['BRAND'] ?></td>
        <td><?= $value['ARTICLE'] ?></td>
        <td><?= $value['ARTICLE_DESC'] ?></td>
        <td><?= $value['SOFFICE'] ?></td>
        <td><?= $value['SOFFICE_DESC'] ?></td>
        <td><?= $value['POS_INV'] ?></td>
        <td><?= $value['BILLING_DATE'] ?></td>
        <td><?= $value['QTY'] ?></td>
        <td><?= number_format($value['PRICE'], 0) ?></td>
        <td><?= $value['SLS_CD'] ?></td>
        <td><?= $value['SLS_NAME'] ?></td>
        <td><?= $value['INC_BIG'] ?></td>
        <td><?= $value['INC_PERC'] ?></td>
        <td><?= $value['INC_PREMIUM'] ?></td>
    </tr>
    <?php $no++; ?>
<?php endforeach; ?>