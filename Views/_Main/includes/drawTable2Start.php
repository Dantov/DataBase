<table class="table table-striped table-bordered table-hover tableWC" style="background: #edf9ff">
    <thead>
        <tr>
            <th colspan="5">Наименование Коллекции: <?=$_SESSION['assist']['collectionName']?></th>
            <th colspan="3">Дата: <?=$this->formatDate(time())?></th>
        </tr>
    </thead>
    <tbody>
    <tr class="text-bold">
        <td>Артикул / №3Д</td>
        <td>Наименование</td>
        <td>Конечный рабочий центр нахождения</td>
        <td>Статус</td>
        <td>Кол-во арт. в коллекции шт.</td>
        <td>Кол-во готовых арт. шт.</td>
        <td>Остаток артикулов</td>
        <td>Дата</td>
    </tr>