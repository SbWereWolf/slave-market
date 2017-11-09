/* Уставить таблицы каталога рабов */

/* Структура каталога, отражает вложенность категорий друг в друга */
CREATE TABLE structure
(
  id           SERIAL PRIMARY KEY NOT NULL,
  structure_id INTEGER,
  code         TEXT,
  name         TEXT,
  description  TEXT,
  insert_date  TIMESTAMP WITH TIME ZONE DEFAULT now(),
  is_hidden    INTEGER                  DEFAULT 0,
  CONSTRAINT fk_structure_structure_id FOREIGN KEY (structure_id) REFERENCES structure (id)
);
COMMENT ON COLUMN structure.id IS 'идентификатор записи';
COMMENT ON COLUMN structure.structure_id IS 'ссылка на родительский элемент';
COMMENT ON COLUMN structure.code IS 'код элемента ( узла древовидной структуры )';
COMMENT ON COLUMN structure.name IS 'имя элемента';
COMMENT ON COLUMN structure.description IS 'описание элемента';
COMMENT ON COLUMN structure.insert_date IS 'дата добавления записи';
COMMENT ON COLUMN structure.is_hidden IS 'флаг "запись является скрытой"';
CREATE UNIQUE INDEX ux_structure_code
  ON structure (code);
CREATE INDEX ix_structure_is_hidden_code
  ON structure (is_hidden, code);
CREATE INDEX ix_structure_is_hidden_id
  ON structure (is_hidden, code);

/* категории */
CREATE TABLE rubric
(
  id          SERIAL PRIMARY KEY NOT NULL,
  code        TEXT,
  name        TEXT,
  description TEXT,
  insert_date TIMESTAMP WITH TIME ZONE DEFAULT now(),
  is_hidden   INTEGER                  DEFAULT 0
);
COMMENT ON COLUMN rubric.id IS 'идентификатор рубрики';
COMMENT ON COLUMN rubric.code IS 'код записи';
COMMENT ON COLUMN rubric.name IS 'наимнование';
COMMENT ON COLUMN rubric.description IS 'описание';
COMMENT ON COLUMN rubric.insert_date IS 'дата добавления записи';
COMMENT ON COLUMN rubric.is_hidden IS 'является скрытым';
CREATE UNIQUE INDEX ux_rubric_code
  ON rubric (code);
CREATE INDEX ix_rubric_is_hidden_id
  ON rubric (is_hidden, id);
CREATE INDEX ix_rubric_is_hidden_code
  ON rubric (is_hidden, code);

/* соответствие категорий элементам структуры */
CREATE TABLE rubric_structure
(
  id           SERIAL PRIMARY KEY NOT NULL,
  rubric_id    INTEGER            NOT NULL,
  structure_id INTEGER            NOT NULL,
  CONSTRAINT fk_rubric_structure_rubric_id FOREIGN KEY (rubric_id) REFERENCES rubric (id),
  CONSTRAINT fk_rubric_structure_structure_id FOREIGN KEY (structure_id) REFERENCES structure (id)
);
COMMENT ON COLUMN rubric_structure.id IS 'идентификатор';
COMMENT ON COLUMN rubric_structure.rubric_id IS 'рубрика';
COMMENT ON COLUMN rubric_structure.structure_id IS 'элемент структуры';
CREATE UNIQUE INDEX ux_rubric_structure_rubric_id_structure_id
  ON rubric_structure (rubric_id, structure_id);

/* все возможные свойства позиций каталога  */
CREATE TABLE information_property
(
  id          SERIAL PRIMARY KEY NOT NULL,
  code        TEXT,
  name        TEXT,
  description TEXT,
  is_hidden   INTEGER                  DEFAULT 0,
  insert_date TIMESTAMP WITH TIME ZONE DEFAULT now()
);
COMMENT ON COLUMN information_property.id IS 'идентификатор свойства информации';
COMMENT ON COLUMN information_property.name IS 'имя';
COMMENT ON COLUMN information_property.description IS 'описание';
COMMENT ON COLUMN information_property.code IS 'код';
COMMENT ON COLUMN information_property.is_hidden IS 'флаг "является скрытым"';
COMMENT ON COLUMN information_property.insert_date IS 'дата добавления записи';
CREATE UNIQUE INDEX ux_information_property_code
  ON information_property (code);
CREATE INDEX ix_information_property_is_hidden_id
  ON information_property (is_hidden, id);
CREATE INDEX ix_information_property_is_hidden_code
  ON information_property (is_hidden, code);

/* соответствие того у каких категорий какие свойства */
CREATE TABLE rubric_information_property
(
  id                      SERIAL PRIMARY KEY NOT NULL,
  rubric_id               INTEGER            NOT NULL,
  information_property_id INTEGER            NOT NULL,
  CONSTRAINT fk_rubric_information_property_rubric_id FOREIGN KEY (rubric_id) REFERENCES rubric (id),
  CONSTRAINT fk_rubric_information_property_information_property_id FOREIGN KEY (information_property_id) REFERENCES information_property (id)
);
COMMENT ON COLUMN rubric_information_property.id IS 'идентификатор';
COMMENT ON COLUMN rubric_information_property.rubric_id IS 'рубирка';
COMMENT ON COLUMN rubric_information_property.information_property_id IS 'свойство информации';
CREATE UNIQUE INDEX "ux_rubric_Information_property_rubric_id_information_property_i"
  ON rubric_information_property (information_property_id, rubric_id);

/* конкретные позиции категорий */
CREATE TABLE rubric_position
(
  rubric_id   INTEGER            NOT NULL,
  id          SERIAL PRIMARY KEY NOT NULL,
  code        TEXT,
  name        TEXT,
  description TEXT,
  is_hidden   INTEGER                  DEFAULT 0,
  insert_date TIMESTAMP WITH TIME ZONE DEFAULT now(),
  CONSTRAINT fk_rubric_position_rubric_id FOREIGN KEY (rubric_id) REFERENCES rubric (id)
);
COMMENT ON COLUMN rubric_position.rubric_id IS 'ссылка на рубрику';
COMMENT ON COLUMN rubric_position.id IS 'идентификатор';
COMMENT ON COLUMN rubric_position.code IS 'код';
COMMENT ON COLUMN rubric_position.name IS 'имя';
COMMENT ON COLUMN rubric_position.description IS 'описание';
COMMENT ON COLUMN rubric_position.is_hidden IS 'флаг "является скрытым"';
COMMENT ON COLUMN rubric_position.insert_date IS 'дата добавления записи';
CREATE UNIQUE INDEX ux_rubric_position_code
  ON rubric_position (code);
CREATE INDEX ix_rubric_position_is_hidden_id
  ON rubric_position (is_hidden, id);
CREATE INDEX ix_rubric_position_is_hidden_code
  ON rubric_position (is_hidden, code);

/* значения свойств конкретных позиций ( пользовательский ввод ) */
CREATE TABLE property_content
(
  rubric_position_id      INTEGER            NOT NULL,
  id                      SERIAL PRIMARY KEY NOT NULL,
  information_property_id INTEGER            NOT NULL,
  content                 TEXT,
  is_hidden               INTEGER                  DEFAULT 0,
  insert_date             TIMESTAMP WITH TIME ZONE DEFAULT now(),
  CONSTRAINT fk_property_content_rubric_position_id FOREIGN KEY (rubric_position_id) REFERENCES rubric_position (id),
  CONSTRAINT fk_property_content_information_property_id FOREIGN KEY (information_property_id) REFERENCES information_property (id)
);
COMMENT ON COLUMN property_content.rubric_position_id IS 'ссылка на позицию рубрики';
COMMENT ON COLUMN property_content.id IS 'идентификатор';
COMMENT ON COLUMN property_content.information_property_id IS 'ссылка на свойство';
COMMENT ON COLUMN property_content.content IS 'содержание ( значение ) свойства';
COMMENT ON COLUMN property_content.is_hidden IS 'флаг "является скрытым"';
COMMENT ON COLUMN property_content.insert_date IS 'дата добавления записи';
CREATE UNIQUE INDEX ux_property_content_rubric_position_id_information_property_id
  ON property_content (rubric_position_id, information_property_id);
CREATE INDEX ix_property_content_is_hidden_id
  ON property_content (is_hidden, id);

/* значения свойств строкового типа ( приведение пользовательского ввода к строке ) */
CREATE TABLE string_content
(
  property_content_id INTEGER            NOT NULL,
  id                  SERIAL PRIMARY KEY NOT NULL,
  string              TEXT,
  is_hidden           INTEGER                  DEFAULT 0,
  insert_date         TIMESTAMP WITH TIME ZONE DEFAULT now(),
  CONSTRAINT fk_string_content_property_content_id FOREIGN KEY (property_content_id) REFERENCES property_content (id)
);
COMMENT ON COLUMN string_content.property_content_id IS 'ссылка на запись допольнительного значения';
COMMENT ON COLUMN string_content.id IS 'идентификатор';
COMMENT ON COLUMN string_content.string IS 'строковое дополнительное значение';
COMMENT ON COLUMN string_content.is_hidden IS 'флаг "является скрытым"';
COMMENT ON COLUMN string_content.insert_date IS 'дата добавления записи';
CREATE INDEX ix_string_content_is_hidden_id
  ON string_content (is_hidden, id);

/* значения свойств числового типа ( приведение пользовательского ввода к числу ) */
CREATE TABLE digital_content
(
  property_content_id INTEGER            NOT NULL,
  id                  SERIAL PRIMARY KEY NOT NULL,
  digital             DOUBLE PRECISION,
  is_hidden           INTEGER                  DEFAULT 0,
  insert_date         TIMESTAMP WITH TIME ZONE DEFAULT now(),
  CONSTRAINT fk_digital_content_property_content_id FOREIGN KEY (property_content_id) REFERENCES property_content (id)
);
COMMENT ON COLUMN digital_content.property_content_id IS 'ссылка на запись допольнительного значения';
COMMENT ON COLUMN digital_content.id IS 'идентификатор';
COMMENT ON COLUMN digital_content.digital IS 'числовое дополнительное значение';
COMMENT ON COLUMN digital_content.is_hidden IS 'флаг "является скрытым"';
COMMENT ON COLUMN digital_content.insert_date IS 'дата добавления записи';
CREATE INDEX ix_digital_content_is_hidden_id
  ON digital_content (is_hidden, id);
