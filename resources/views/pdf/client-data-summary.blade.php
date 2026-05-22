<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Résumé des données - {{ $client->full_name }}</title></head>
<body style="font-family:sans-serif;max-width:800px;margin:auto;padding:20px;">
    <h1>Résumé des données personnelles</h1>
    <p>Conformément à la Loi 09-08, voici l'ensemble des données détenues.</p>

    <h2>Informations personnelles</h2>
    <ul>
        <li>Nom : {{ $client->full_name }}</li>
        <li>Email : {{ $client->email }}</li>
        <li>Téléphone : {{ $client->phone ?? 'Non renseigné' }}</li>
        <li>Langue : {{ $client->preferred_locale }}</li>
        <li>Dernière connexion : {{ $client->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}</li>
    </ul>

    <h2>Rendez-vous ({{ $bookings->count() }})</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
        <thead><tr>
            <th>Référence</th><th>Date</th><th>Formule</th><th>Format</th><th>Statut</th><th>Montant</th>
        </tr></thead>
        <tbody>
        @foreach ($bookings as $booking)
            <tr>
                <td>{{ $booking->reference }}</td>
                <td>{{ $booking->starts_at->format('d/m/Y H:i') }}</td>
                <td>{{ $booking->plan?->name ?? '-' }}</td>
                <td>{{ $booking->format }}</td>
                <td>{{ $booking->status }}</td>
                <td>{{ $booking->total_centimes ? number_format($booking->total_centimes/100,2,',',' ').' MAD' : '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
