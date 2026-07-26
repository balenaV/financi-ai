<x-app-layout>
    <x-slot name="title">Histórico de segurança</x-slot>
    <x-page-header title="Histórico de segurança" description="Ações que alteraram dados da sua conta." />
    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Data</th><th class="px-5 py-3">Ação</th><th class="px-5 py-3">Rota</th><th class="px-5 py-3">IP</th><th class="px-5 py-3">Resultado</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr><td class="px-5 py-4 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td><td class="px-5 py-4">{{ ucfirst($log->event) }}</td><td class="px-5 py-4">{{ $log->route ?? '—' }}</td><td class="px-5 py-4">{{ $log->ip_address ?? '—' }}</td><td class="px-5 py-4">{{ $log->metadata['status'] ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Nenhuma alteração registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
