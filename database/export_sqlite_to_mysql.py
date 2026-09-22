#!/usr/bin/env python3
"""
Exporta la base de datos SQLite de FERPLEC a un script SQL compatible con MySQL/MariaDB.
Uso: python3 database/export_sqlite_to_mysql.py
"""

import sqlite3
import re
from pathlib import Path

BASE_DIR = Path(__file__).parent.parent
DB_PATH = BASE_DIR / 'database' / 'ferplec.sqlite'
OUTPUT_PATH = BASE_DIR / 'database' / 'ferplec_export.sql'

TIPOS_SQLITE_A_MYSQL = {
    'integer': 'bigint unsigned',
    'int': 'int',
    'tinyint': 'tinyint',
    'varchar': 'varchar(255)',
    'char': 'char(36)',
    'text': 'text',
    'longtext': 'longtext',
    'datetime': 'datetime',
    'timestamp': 'timestamp',
    'date': 'date',
    'decimal': 'decimal(12,2)',
    'float': 'float',
    'double': 'double',
    'boolean': 'tinyint(1)',
    'json': 'json',
}


def convertir_tipo(tipo_sqlite: str) -> str:
    tipo = tipo_sqlite.lower().strip()

    # Tipos con precision declarada, ej: decimal(12,2), varchar(255)
    match = re.match(r'^(\w+)(\([^)]+\))?', tipo)
    if not match:
        return 'text'

    base = match.group(1)
    precision = match.group(2) or ''

    if base in ('integer',):
        return 'bigint unsigned'
    if base in ('varchar',):
        return f'varchar{precision}' if precision else 'varchar(255)'
    if base in ('char',):
        return f'char{precision}' if precision else 'char(36)'
    if base in ('decimal', 'numeric'):
        return f'decimal{precision}' if precision else 'decimal(12,2)'
    if base in ('tinyint',):
        return 'tinyint(1)' if not precision else f'tinyint{precision}'
    if base in ('bigint',):
        return f'bigint unsigned{precision}'

    return TIPOS_SQLITE_A_MYSQL.get(base, 'text')


def escapar_valor(valor) -> str:
    if valor is None:
        return 'NULL'
    if isinstance(valor, bytes):
        return f"0x{valor.hex()}"
    if isinstance(valor, (int, float)):
        return str(valor)
    texto = str(valor).replace("\\", "\\\\").replace("'", "\\'")
    return f"'{texto}'"


def main():
    if not DB_PATH.exists():
        print(f'No se encontro la base de datos: {DB_PATH}')
        return

    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()

    lineas = [
        '-- Exportacion de FERPLEC desde SQLite a MySQL/MariaDB',
        'SET FOREIGN_KEY_CHECKS = 0;',
        'SET NAMES utf8mb4;',
        '',
    ]

    cursor.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
    tablas = [row['name'] for row in cursor.fetchall()]

    for tabla in tablas:
        cursor.execute(f'PRAGMA table_info("{tabla}")')
        columnas = cursor.fetchall()

        defs = []
        primary = []
        unicas = []

        for col in columnas:
            nombre = col['name']
            tipo_mysql = convertir_tipo(col['type'])
            nullable = 'NULL' if col['notnull'] == 0 else 'NOT NULL'
            default = ''
            if col['dflt_value'] is not None:
                raw = col['dflt_value']
                # SQLite guarda los defaults como strings, a veces con comillas simples escapadas
                if isinstance(raw, str):
                    limpio = raw
                    while limpio.startswith("'") and limpio.endswith("'"):
                        limpio = limpio[1:-1]
                    limpio = limpio.replace("''", "'")

                    if limpio.lower() == 'true':
                        default = ' DEFAULT 1'
                    elif limpio.lower() == 'false':
                        default = ' DEFAULT 0'
                    elif limpio.isdigit():
                        default = f' DEFAULT {limpio}'
                    elif re.match(r'^-?\d+(\.\d+)?$', limpio):
                        default = f' DEFAULT {limpio}'
                    elif limpio.lower() == 'null':
                        default = ' DEFAULT NULL'
                    elif limpio.lower() == 'current_timestamp':
                        default = ' DEFAULT CURRENT_TIMESTAMP'
                    else:
                        default = f" DEFAULT {escapar_valor(limpio)}"
                else:
                    default = f" DEFAULT {escapar_valor(raw)}"

            extra = ''
            if nombre == 'id' and tipo_mysql == 'bigint unsigned':
                extra = ' AUTO_INCREMENT'

            defs.append(f"    `{nombre}` {tipo_mysql} {nullable}{default}{extra}")

            if col['pk'] == 1:
                primary.append(f'`{nombre}`')

        if primary:
            defs.append(f"    PRIMARY KEY ({', '.join(primary)})")

        lineas.append(f'DROP TABLE IF EXISTS `{tabla}`;')
        lineas.append(f'CREATE TABLE `{tabla}` (')
        lineas.append(',\n'.join(defs))
        lineas.append(') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;')
        lineas.append('')

        cursor.execute(f'SELECT * FROM "{tabla}"')
        filas = cursor.fetchall()

        if not filas:
            continue

        nombres_cols = [col['name'] for col in columnas]
        cols_sql = ', '.join(f'`{c}`' for c in nombres_cols)

        # Insertar en lotes de 100 para no hacer lineas gigantes
        batch = []
        for fila in filas:
            valores = ', '.join(escapar_valor(fila[c]) for c in nombres_cols)
            batch.append(f'({valores})')

            if len(batch) >= 100:
                lineas.append(f'INSERT INTO `{tabla}` ({cols_sql}) VALUES')
                lineas.append(',\n'.join(batch) + ';')
                lineas.append('')
                batch = []

        if batch:
            lineas.append(f'INSERT INTO `{tabla}` ({cols_sql}) VALUES')
            lineas.append(',\n'.join(batch) + ';')
            lineas.append('')

    lineas.extend([
        'SET FOREIGN_KEY_CHECKS = 1;',
        '',
    ])

    OUTPUT_PATH.write_text('\n'.join(lineas), encoding='utf-8')
    conn.close()

    print(f'Exportacion guardada en: {OUTPUT_PATH}')
    print(f'Tablas exportadas: {len(tablas)}')


if __name__ == '__main__':
    main()
