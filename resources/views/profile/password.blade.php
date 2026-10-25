<x-layouts.app title="Ganti Password">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Ganti Password</h1>
        <p class="mt-1 text-sm text-slate-500">Gunakan password lama untuk mengatur password baru akun Anda.</p>
    </div>

    <form action="{{ route('profile.password.update') }}" method="POST"
        class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-md">
        @csrf
        @method('PUT')

        <div class="mb-5">
            <label for="password_lama" class="mb-1 block text-sm font-semibold text-slate-700">Password lama</label>
            <input id="password_lama" name="password_lama" type="password" autocomplete="current-password" required
                class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-primary focus:outline-none @error('password_lama') border-red-500 @enderror">
            @error('password_lama')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-5">
            <label for="password_baru" class="mb-1 block text-sm font-semibold text-slate-700">Password baru</label>
            <input id="password_baru" name="password_baru" type="password" autocomplete="new-password" required
                class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-primary focus:outline-none @error('password_baru') border-red-500 @enderror">
            <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter dan harus berbeda dari password lama.</p>
            @error('password_baru')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6">
            <label for="password_baru_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Konfirmasi password baru</label>
            <input id="password_baru_confirmation" name="password_baru_confirmation" type="password" autocomplete="new-password" required
                class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-primary focus:outline-none">
        </div>

        <button type="submit" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
            Simpan password baru
        </button>
    </form>
</x-layouts.app>
