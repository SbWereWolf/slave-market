/* Заполнение каталога данными */

/* Составление структуры категорий */

/* элемент структуры для категория Аренда */
INSERT INTO structure (code, name)
VALUES ('arenda', 'Услуги аренды')
RETURNING id;

/* элементы структуры категорий для подкатегорий категории Аренда */
INSERT INTO structure (structure_id, code, name)
VALUES
  (1, 'slaves', 'Рабы'),
  (1, 'employee', 'Наёмники'),
  (1, 'trucks', 'Грузовики')
RETURNING code, id;
/*
slaves,2
employee,3
trucks,4
*/

/* элементы структуры для подкатегорий подкатегории Рабы */
INSERT INTO structure (structure_id, code, name)
VALUES
  (2, 'agriculture', 'земледелие'),
  (2, 'cattle_breeding', 'скотоводство'),
  (2, 'work_at_home', 'работа по дому'),
  (2, 'work_at_quarry', 'работа в каменоломне')
RETURNING code, id;

/*
agriculture,5
cattle_breeding,6
work_at_home,7
work_at_quarry,8
*/

/* элементы структуры для категории 'работа по дому' */
INSERT INTO structure (structure_id, code, name)
VALUES
  (7, 'cleaning', 'уборка'),
  (7, 'cooking', 'приготовление пищи'),
  (7, 'For_kitchen', 'Для кухни')
RETURNING code, id;
/*
cleaning,9
cooking,10
For_kitchen,11
*/

/* элементы структуры для категории 'Для кухни' */
INSERT INTO structure (structure_id, code, name)
VALUES
  (11, 'Washing_dishes', 'Мытьё посуды'),
  (11, 'Cutting_bread', 'Резка хлеба'),
  (11, 'Kneading_dough', 'Замес теста'),
  (11, 'Baking_cakes', 'Выпекание пирогов')
RETURNING code, id;
/*
Washing_dishes,12
Cutting_bread,13
Kneading_dough,14
Baking_cakes,15
*/

/* собственно категории */
INSERT INTO rubric (code, name)
VALUES
  ('Washing_dishes', 'Мытьё посуды'),
  ('Cutting_bread', 'Резка хлеба'),
  ('Kneading_dough', 'Замес теста'),
  ('Baking_cakes', 'Выпекание пирогов')
RETURNING code, id;
/*
Washing_dishes,1
Cutting_bread,2
Kneading_dough,3
Baking_cakes,4
*/

/* соединяем категории с элементами структуры категорий */
INSERT INTO rubric_structure (rubric_id, structure_id)
VALUES
  (1, 12),
  (2, 13),
  (3, 14),
  (4, 15)
RETURNING id;
;

/* все возможные свойства каталога */
INSERT INTO information_property (code, name)
VALUES
  ('nickname', 'Кличка'),
  ('sex', 'Пол'),
  ('age', 'Возраст'),
  ('weight', 'Вес'),
  ('skin', 'Цвет кожи'),
  ('location', 'Где пойман/выращен'),
  ('description', 'Описание и повадки'),
  ('rate', 'Ставка почасовой аренды'),
  ('price', 'Стоимость')
RETURNING code, id;
/*
nickname,1
sex,2
age,3
weight,4
skin,5
location,6
description,7
rate,8
price,9
*/

/* назначаем категориям возможные свойства , поскольку набор свойств один на всех, то разница только в rubric_id
был бы я ленивый сделал бы через WITH кода было бы в четыре раза меньше */
INSERT INTO rubric_information_property
(rubric_id, information_property_id)
VALUES
  (1, 1),
  (2, 1),
  (3, 1),
  (4, 1),
  (1, 2),
  (2, 2),
  (3, 2),
  (4, 2),
  (1, 3),
  (2, 3),
  (3, 3),
  (4, 3),
  (1, 4),
  (2, 4),
  (3, 4),
  (4, 4),
  (1, 5),
  (2, 5),
  (3, 5),
  (4, 5),
  (1, 6),
  (2, 6),
  (3, 6),
  (4, 6),
  (1, 7),
  (2, 7),
  (3, 7),
  (4, 7),
  (1, 8),
  (2, 8),
  (3, 8),
  (4, 8),
  (1, 9),
  (2, 9),
  (3, 9),
  (4, 9);
/*
36 rows affected in 19ms
*/

/* позиции подкатегорий категории 'Для кухни'
когда давал имена, ещё не понимал, что часть из позиций окажется женского пола, поэтому все имена мужские */
INSERT INTO rubric_position
(rubric_id, code, name)
VALUES
  (1, 'Vasya', 'Вася'),
  (1, 'Petya', 'Петя'),
  (1, 'Fedya', 'Федя'),
  (1, 'Seryozha', 'Серёжа'),
  (1, 'Antosha', 'Антоша'),
  (1, 'Oleja', 'Олежа'),

  (2, 'Mishanya', 'Мишаня'),
  (2, 'Basil', 'Василий'),
  (2, 'Peter', 'Пётр'),
  (2, 'Fedor', 'Фёдор'),
  (2, 'Sergey Seryozhkin', 'Сергей Сёрёжкин'),
  (2, 'Antosha Chehonte', 'Антоша Чехонте'),

  (3, 'Sergey', 'Сергей'),
  (3, 'Anton', 'Антон'),
  (3, 'Oleg', 'Олег'),
  (3, 'Michael', 'Михаил'),

  (4, 'Vasily Alibabaevich', 'Василий Алибабаевич'),
  (4, 'Pyotr Fedorovich', 'Пётр Федорович')
;
/*
18 rows affected in 12ms
*/

/* значения для свойства 'Кличка' отдельное для каждой позиции */
with property AS
(
  select id as id from information_property where code = 'nickname'
)
INSERT INTO property_content
(rubric_position_id, information_property_id, content)
    VALUES
      (1,(select id from property),'жаренный'),
      (2,(select id from property),'варенный'),
      (3,(select id from property),'паренный'),
      (4,(select id from property),'счатье'),
      (5,(select id from property),'стена'),
      (6,(select id from property),'голова'),
      (7,(select id from property),'режик'),
      (8,(select id from property),'тормоз'),
      (9,(select id from property),'кусок'),
      (10,(select id from property),'быстрый'),
      (11,(select id from property),'шустрик'),
      (12,(select id from property),'красный'),
      (13,(select id from property),'грек'),
      (14,(select id from property),'калека'),
      (15,(select id from property),'чемпион'),
      (16,(select id from property),'друг'),
      (17,(select id from property),'зэк'),
      (18,(select id from property),'жэк')
;

/* property_content это собственно пользовательский ввод,
каталог в своей работе использует приведённую к какому либо типу информацию,
для свойства 'Кличка' пользовательский ввод приводиться к строковому представлению
*/
INSERT INTO string_content
    (property_content_id, string )
  SELECT pc.id, pc.content
  FROM property_content pc
    JOIN information_property ip
      ON pc.information_property_id = ip.id
WHERE
  ip.code = 'nickname'
;

/* заполняем значениями свойство "Пол" */
with property AS
(
  select id as id from information_property where code = 'sex'
)
INSERT INTO property_content
(rubric_position_id, information_property_id, content)
    VALUES
      (1,(select id from property),'M'),
      (2,(select id from property),'M'),
      (3,(select id from property),'M'),
      (4,(select id from property),'F'),
      (5,(select id from property),'M'),
      (6,(select id from property),'F'),
      (7,(select id from property),'M'),
      (8,(select id from property),'M'),
      (9,(select id from property),'F'),
      (10,(select id from property),'F'),
      (11,(select id from property),'M'),
      (12,(select id from property),'F'),
      (13,(select id from property),'M'),
      (14,(select id from property),'M'),
      (15,(select id from property),'M'),
      (16,(select id from property),'F'),
      (17,(select id from property),'M'),
      (18,(select id from property),'F')
;
/* "Пол" конечно должен быть справочником, но учитывая не самую стандартную схему каталога,
было лень огород городить с красивыми справочниками, для тестовго задания достаточно, для продакшена подумаем */
INSERT INTO string_content
    (property_content_id, string )
  SELECT pc.id, pc.content
  FROM property_content pc
    JOIN information_property ip
      ON pc.information_property_id = ip.id
WHERE
  ip.code = 'sex'
;

/* свойство "вес" */
with property AS
(
  select id as id from information_property where code = 'weight'
)
INSERT INTO property_content
(rubric_position_id, information_property_id, content)
    VALUES
      (1,(select id from property),'80'),
      (2,(select id from property),'95'),
      (3,(select id from property),'55'),
      (4,(select id from property),'45'),
      (5,(select id from property),'65'),
      (6,(select id from property),'55'),
      (7,(select id from property),'73'),
      (8,(select id from property),'62'),
      (9,(select id from property),'66'),
      (10,(select id from property),'89'),
      (11,(select id from property),'84'),
      (12,(select id from property),'59'),
      (13,(select id from property),'36'),
      (14,(select id from property),'64'),
      (15,(select id from property),'91'),
      (16,(select id from property),'43'),
      (17,(select id from property),'84'),
      (18,(select id from property),'48')
;
/* значения свойства "вес" приводим к целочисленному виду , сохраняем в таблице для числовых значений */
INSERT INTO digital_content
    (property_content_id, digital)
  SELECT pc.id, pc.content::INT
  FROM property_content pc
    JOIN information_property ip
      ON pc.information_property_id = ip.id
WHERE
  ip.code = 'weight'
;

/* значения для свойства Стоимость раба, мне показалось что price ( "цена" ) более точно отражает суть */
with property AS
(
  select id as id from information_property where code = 'price'
)
INSERT INTO property_content
(rubric_position_id, information_property_id, content)
    VALUES
      (1,(select id from property),'80'),
      (2,(select id from property),'95'),
      (3,(select id from property),'55'),
      (4,(select id from property),'45'),
      (5,(select id from property),'65'),
      (6,(select id from property),'55'),
      (7,(select id from property),'73'),
      (8,(select id from property),'62'),
      (9,(select id from property),'66'),
      (10,(select id from property),'89'),
      (11,(select id from property),'84'),
      (12,(select id from property),'59'),
      (13,(select id from property),'36'),
      (14,(select id from property),'64'),
      (15,(select id from property),'91'),
      (16,(select id from property),'43'),
      (17,(select id from property),'84'),
      (18,(select id from property),'48')
;
/* собираемся использовать как число, поэтому сохраняем в таблицу для числовых значений */
INSERT INTO digital_content
    (property_content_id, digital)
  SELECT pc.id, pc.content::INT
  FROM property_content pc
    JOIN information_property ip
      ON pc.information_property_id = ip.id
WHERE
  ip.code = 'price'
;
