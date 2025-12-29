{{-- resources/views/statistics/partials/edit_goal_modal_content.blade.php --}}

<div class="modal-header">
    <h5 class="modal-title" id="staticEditGoalModalLabel">Modifier l'Objectif : {{ $goal->type_label }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    {{-- La classe 'editGoalForm' est utilisée par le JS pour attacher l'écouteur de soumission AJAX --}}
    <form class="editGoalForm row g-3" action="{{ route('manager.goals.update', ['goal' => $goal->token]) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Type d'objectif (Modifiable) --}}
        <div class="col-md-4">
            <label for="type" class="form-label">Type d'Objectif</label>
            <select name="type" id="type" class="form-select" required>
                {{-- Définir toutes les options --}}
                @php
                    $types = [
                        'deposits' => 'Nombre de Dépôts',
                        'revenue' => 'Chiffre d\'Affaires',
                        'deliveries' => 'Nombre de Livraisons',
                        'new_clients' => 'Nouveaux Clients',
                        'charges' => 'Charges Totales (à minimiser)',
                    ];
                    $currentType = old('type', $goal->type);
                @endphp

                @foreach($types as $value => $label)
                    <option value="{{ $value }}" {{ $currentType == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            {{-- Pour l'affichage des erreurs AJAX --}}
            <div class="invalid-feedback" data-field="type"></div> 
        </div>

        {{-- Périodicité (Lecture seule) --}}
        <div class="col-md-2">
            <label class="form-label">Période</label>
            <input type="text" class="form-control" value="{{ ucfirst($goal->periodicity) }}" disabled>
            <input type="hidden" name="periodicity" value="{{ $goal->periodicity }}">
        </div>
        
        {{-- Valeur Cible --}}
        <div class="col-md-3">
            <label for="target_value" class="form-label">Valeur Cible</label>
            <input type="number" step="0.01" name="target_value" id="target_value" class="form-control" value="{{ old('target_value', $goal->target_value) }}" required>
            {{-- L'attribut data-field est crucial pour l'affichage des erreurs AJAX --}}
            <div class="invalid-feedback" data-field="target_value"></div>
        </div>

        {{-- Utilisateur Cible --}}
        <div class="col-md-3">
            <label for="user_token" class="form-label">Attribué à</label>
            <select name="user_token" id="user_token" class="form-select">
                <option value="">-- Tout le Pressing --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->token }}" {{ old('user_token', $goal->user_token) == $employee->token ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback" data-field="user_token"></div>
        </div>

        {{-- Date de Début --}}
        <div class="col-md-3">
            <label for="start_date" class="form-label">Date de Début</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $goal->start_date->toDateString()) }}" required>
            <div class="invalid-feedback" data-field="start_date"></div>
        </div>

        {{-- Date de Fin --}}
        <div class="col-md-3">
            <label for="end_date" class="form-label">Date de Fin</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $goal->end_date->toDateString()) }}" required>
            <div class="invalid-feedback" data-field="end_date"></div>
        </div>
        
        <div class="col-12 mt-4 text-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
            {{-- Ce bouton recevra le loader Spinner lors de la soumission --}}
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>