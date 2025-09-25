<?php
//  [id, count, price, pack]
$priceList = [
    [111, 42, 13, 1],
    [222, 77, 11, 10],
    [333, 103, 10, 50],
    [444, 65, 12, 5],
];
$priceList = [
    [111,42,9,1],
    [222,77,11,10],
    [333,103,10,50],
    [444,65,12,5],
];
$priceList = [
    [111, 100, 30, 1],
    [222, 60, 11, 10],
    [333, 100, 13, 50],
];

// эта переменная содержит желаемое количество товаров для закупки
$amountTarget = 76;

function printPriceList($priceList)
{
    echo "Price list:\n";
    echo "[id, count, price, pack]\n";
    foreach ($priceList as $data) {
        echo json_encode($data) . "\n";
    }
    echo "\n";
}
function printResult($result)
{
    echo "Order plan:\n";
    $plan = [];
    foreach ($result as $id => $data) {
        $plan[] = [$id, $data[0]];
    }
    echo json_encode($plan) . "\n";
}


// сюда сохраним найденный "план" закупки,
// имеет формат [[id1 => [amount11, price1]], [id2 => amount22, price2] ...]
$result = [];
// Эту переменную будем использовать для поиска самого дешегового плана
$minPrice = null;

// будем использовать рекурсию для поиска плана закупки,
// эта функция обхода прайса и сбора "плана" для закупки,
// тут же ищем самы дешевый план
//
// $priceList - двумерный массив прайса
// $amountTarget - необходимый кол-во товара на этой интерации обхода
// $start - строка в списке прайса, с которой начинаем обход
// $path - сюда собираем план закупки в формате [[id1 => amount11], [id2 => amount22] ...]
// $result - сюда запиши окончатеьный вариант "пална" закупки
// переменная для поиска плана с минимальной стоимостью
function findCombinations($priceList, $amountTarget, $start, $path, &$result, &$minPrice)
{
    // если в текущей итерации необходимое кол-во для заказа товара 0,
    // то $path содержит завершенный план закупки
    if ($amountTarget == 0) {

        // проверим является ли этот план с минимальной стоимостью
        $sum = 0;
        foreach ($path as $id => $data) {
            $sum += $data[0] * $data[1];
        }

        // если минимальная стоимость у нового плана
        if (empty($minPrice) || $minPrice > $sum) {
            $result = $path; // нашли комбинацию
            $minPrice = $sum;
        }

        return;
    }

    for ($i = $start; $i < count($priceList); $i++) {
        list($id, $count, $price, $pack) = $priceList[$i];
        // если кол-во товаров в пакете больше количества товаров на складе,
        // тогда не можем продать товар
        if ($pack > $count) {
            continue; // нет в наличии
        }

        if ($pack > $amountTarget) {
            continue; // предложение превышает спрос, не будем анализировать
        }

        // определим сколько пакетов товара может продать этот поставщшик
        $packAmount = floor($count / $pack);

        $amount = 0;
        // начнем перебирать возможный заказ пактов от максимального кол-во пакетов
        for ($p = $packAmount; $p >= 1; $p--) {
            $amount = $p * $pack;
            if ($amount > $amountTarget) {
                continue; // превышаем целевое кол-во
            }
            // используем пакеты с общим кол-вом $amount
            // и подберем комбинации из прайсов следующих поставщиков
            findCombinations(
                $priceList,
                $amountTarget - $amount,
                $i + 1,
                $path + [$id => [$amount, $price]],
                $result,
                $minPrice
            );
        }
    }
}

printPriceList($priceList);

// Найдем комбинацию для оптимального плана закупки
findCombinations($priceList, $amountTarget, 0, [], $result, $minPrice);

printResult($result);
