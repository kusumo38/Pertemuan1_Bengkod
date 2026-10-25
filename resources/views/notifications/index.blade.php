<x-layouts.app title="Notifikasi">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Notifikasi</h1>
        <p class="mt-1 text-sm text-slate-500">Informasi terbaru untuk akun Anda.</p>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border p-4 {{ $notification->read_at ? 'border-slate-200 bg-white' : 'border-blue-200 bg-blue-50' }}">
                <div class="flex-1">
                    <p class="font-semibold text-slate-800">{{ $notification->data['message'] ?? 'Ada pembaruan untuk Anda.' }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                    @if(!empty($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" class="mt-2 inline-block text-sm font-semibold text-blue-700 underline">Buka informasi</a>
                    @endif
                </div>
                @if(! $notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                            Tandai sudah dibaca
                        </button>
                    </form>
                @else
                    <span class="text-xs text-slate-500">Sudah dibaca</span>
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center text-slate-500">
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</x-layouts.app>
