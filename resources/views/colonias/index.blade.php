<x-app-layout>
    @section('title', 'Colonias')
    <x-slot name="header">
        <div class="flex items-center justify-between py-2" style="padding-left: 30px; padding-right: 20px;">
            <div class="flex-shrink-0">
                <h2 class="font-bold text-lg text-slate-900 leading-none mb-0.5">
                    {{ __('Listado de Colonias') }}
                </h2>
                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Catálogo Geográfico</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('colonias.create') }}" class="font-semibold flex items-center shadow-sm px-3 rounded h-8 text-[10px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all uppercase">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Nueva Colonia
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter, 
        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_paginate {
            font-size: 11px !important;
            color: #64748b !important;
            font-weight: 600 !important;
        }
        
        table.dataTable {
            border-collapse: collapse !important;
            font-family: 'Calibri', 'Segoe UI', sans-serif !important;
            font-size: 12px !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        table.dataTable thead th {
            background: linear-gradient(to bottom, #f8fafc, #f1f5f9) !important;
            color: #1e293b !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 10px !important;
            letter-spacing: 0.05em !important;
            border-bottom: 2px solid #cbd5e1 !important;
            padding: 8px 10px !important;
        }

        table.dataTable tbody td {
            padding: 4px 10px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .dataTables_filter input {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 4px 10px !important;
            font-size: 12px !important;
            outline: none !important;
        }
    </style>

    <div class="py-4 bg-slate-50 overflow-hidden" style="height: calc(100vh - 65px);">
        <div class="w-full h-full flex flex-col" style="padding-left: 30px; padding-right: 20px;">
            <div class="bg-white overflow-hidden shadow-sm border border-slate-200 rounded-xl flex flex-col h-full">
                <div class="p-0 overflow-hidden flex-1 relative">
                    <table id="colonias-table" class="table w-100 mb-0 border-t-0">
                        <thead>
                            <tr>
                                <th width="100px">Código</th>
                                <th>Colonia</th>
                                <th width="120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($colonias as $colonia)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-slate-500">{{ $colonia->COD_COL }}</td>
                                <td class="font-semibold text-slate-700">{{ $colonia->COLONIA }}</td>
                                <td>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('colonias.edit', $colonia) }}" class="p-1 text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('colonias.destroy', $colonia) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors" onclick="return confirm('¿Eliminar esta colonia?')" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables CSS/JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#colonias-table').DataTable({
                "pageLength": 100,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                "dom": '<"p-3 px-8 flex justify-between items-center border-b border-slate-100"lf>rt<"p-3 px-8 border-t border-slate-100 flex justify-between items-center"ip>',
                "scrollY": "calc(100vh - 280px)",
                "scrollCollapse": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });
        });
    </script>
</x-app-layout>
