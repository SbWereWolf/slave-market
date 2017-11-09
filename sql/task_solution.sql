
/* Решение задания */


/* Получить минимальную, максимальную и среднюю стоимость всех рабов весом более 60 кг. */
WITH price_data ( price ) AS (
  WITH RECURSIVE structures_ids ( id, structure_id ) AS
  (
    SELECT
      sp.id           AS id,
      sp.structure_id AS structure_id
    FROM
      structure sp
      LEFT JOIN structure sc
        ON sc.structure_id = sp.id
    WHERE sp.code = 'slaves'
    UNION
    SELECT
      si.id,
      si.structure_id
    FROM
      structure si
      JOIN structures_ids sr
        ON sr.id = si.structure_id
  )
  SELECT (
           SELECT dcs.digital
           FROM
             rubric_position rps
             JOIN property_content pcs
               ON rps.id = pcs.rubric_position_id
             JOIN information_property ips
               ON pcs.information_property_id = ips.id
             JOIN digital_content dcs
               ON pcs.id = dcs.property_content_id
           WHERE
             rps.id = rp.id
             AND ips.code = 'price') AS price

  FROM
    structures_ids si
    JOIN rubric_structure rs
      ON si.id = rs.structure_id
    JOIN rubric r
      ON rs.rubric_id = r.id
    JOIN rubric_position rp
      ON r.id = rp.rubric_id
    JOIN property_content pc
      ON rp.id = pc.rubric_position_id
    JOIN information_property ip
      ON pc.information_property_id = ip.id
    JOIN digital_content dc
      ON pc.id = dc.property_content_id
  WHERE
    ip.code = 'weight'
    AND dc.digital > 60
)
SELECT
  MIN(pd.price),
  MAX(pd.price),
  round(AVG(pd.price) :: NUMERIC, 2)
FROM price_data pd;
;

/* Выбрать категории, в которых больше 10 рабов. */
SELECT (
  SELECT rs.name
  FROM rubric rs
  WHERE rs.id = r.id)
FROM
  rubric r
  JOIN rubric_position rp
    ON r.id = rp.rubric_id
GROUP BY r.id
HAVING count(*) > 10;

/* Выбрать категорию с наибольшей суммарной стоимостью рабов. */
WITH rubric_price (price, id) AS (
    SELECT
      SUM(
          (SELECT dcs.digital
           FROM
             rubric_position rps
             JOIN property_content pcs
               ON rps.id = pcs.rubric_position_id
             JOIN digital_content dcs
               ON pcs.id = dcs.property_content_id
             JOIN information_property ips
               ON pcs.information_property_id = ips.id
           WHERE
             ips.code = 'price'
             AND rps.id = rp.id
          )
      ),
      r.id
    FROM
      rubric r
      JOIN rubric_position rp
        ON r.id = rp.rubric_id
    GROUP BY r.id
)
SELECT (SELECT name
        FROM rubric
        WHERE id = rp.id)
FROM rubric_price rp
WHERE price = (SELECT max(price)
               FROM rubric_price);

/* Выбрать категории, в которых мужчин больше чем женщин. */
WITH male_greate_than_female (sex_m, sex_f, id) AS (
  WITH rubric_sex (sex_m, sex_f, id) AS (

      SELECT
        (SELECT count(*)
         FROM
           rubric rs
           JOIN rubric_position rps
             ON rs.id = rps.rubric_id
           JOIN property_content pcs
             ON rps.id = pcs.rubric_position_id
           JOIN string_content dcs
             ON pcs.id = dcs.property_content_id
           JOIN information_property ips
             ON pcs.information_property_id = ips.id
         WHERE
           ips.code = 'sex'
           AND rs.id = r.id
           AND dcs.string = 'M'
        ) sex_m,
        (SELECT count(*)
         FROM
           rubric rs
           JOIN rubric_position rps
             ON rs.id = rps.rubric_id
           JOIN property_content pcs
             ON rps.id = pcs.rubric_position_id
           JOIN string_content dcs
             ON pcs.id = dcs.property_content_id
           JOIN information_property ips
             ON pcs.information_property_id = ips.id
         WHERE
           ips.code = 'sex'
           AND rs.id = r.id
           AND dcs.string = 'F'
        ) sex_f,
        r.id

      FROM
        rubric r
      GROUP BY r.id
  )
  SELECT
    sex_m,
    sex_f,
    id
  FROM rubric_sex
)
SELECT (SELECT name
        FROM rubric r
        WHERE r.id = mf.id)
FROM male_greate_than_female mf
WHERE
  mf.sex_m > mf.sex_f;

/* Количество рабов в категории "Для кухни" (включая все вложенные категории). */
WITH RECURSIVE structures_ids ( id, structure_id ) AS
(
  SELECT
    sp.id           AS id,
    sp.structure_id AS structure_id
  FROM
    structure sp
    LEFT JOIN structure sc
      ON sc.structure_id = sp.id
  WHERE sp.code = 'For_kitchen'
  UNION
  SELECT
    si.id,
    si.structure_id
  FROM
    structure si
    JOIN structures_ids sr
      ON sr.id = si.structure_id
)
SELECT count(*)
FROM
  structures_ids si
  JOIN rubric_structure rs
    ON si.id = rs.structure_id
  JOIN rubric r
    ON rs.rubric_id = r.id
  JOIN rubric_position rp
    ON r.id = rp.rubric_id;

