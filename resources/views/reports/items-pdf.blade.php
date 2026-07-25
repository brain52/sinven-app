<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Inventaris Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.subtitle { text-align: center; margin-top: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; color: #000; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h2>LAPORAN INVENTARIS ASET LABORATORIUM</h2>
    <p class="subtitle">Sistem Informasi Inventaris Sekolah (Sinven-App)</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Inventaris</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->inventory_code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category->name ?? '-' }}</td>
                <td>{{ $item->condition->name ?? '-' }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>