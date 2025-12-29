{{-- resources/views/statistics/partials/goals_table_content.blade.php --}}

<div class="card-body p-0">
    @if ($goals->isEmpty())
        <div class="alert alert-info text-center m-4">
            Aucun objectif n'a été fixé pour l'instant.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Type</th>
                        <th scope="col">Période</th>
                        <th scope="col">Cible</th>
                        <th scope="col">Réalisé</th> 
                        <th scope="col">Début / Fin</th>
                        <th scope="col">Attribué à</th>
                        <th scope="col">Progression</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($goals as $goal)
                        <tr>
                            {{-- Affichage du Type (utilise $goal->type_label du contrôleur) --}}
                            <td>{{ $goal->type_label }}</td>
                            <td>{{ ucfirst($goal->periodicity) }}</td>
                            
                            {{-- Cible avec formatage monétaire si nécessaire --}}
                            <td>{{ number_format($goal->target_value, 0, ',', ' ') }} {{ $goal->is_monetary ? 'XAF' : '' }}</td>
                            
                            {{-- Réalisé (utilise $goal->current_value du contrôleur) --}}
                            <td class="fw-bold text-success">
                                {{ number_format($goal->current_value, 0, ',', ' ') }} {{ $goal->is_monetary ? 'XAF' : '' }}
                            </td>
                            
                            <td>{{ $goal->start_date->format('d/m/Y') }} - {{ $goal->end_date->format('d/m/Y') }}</td>
                            
                            {{-- Attribué à (utilise $goal->user->name si la relation est nommée 'user') --}}
                            <td>{{ $goal->user->name ?? 'Global' }}</td>
                            
                            {{-- Barre de progression (utilise $goal->percentage du contrôleur) --}}
                            <td>
                                @php
                                    $percentage = min($goal->percentage, 100);
                                    $progressBarColor = $goal->status_color; 
                                    
                                    // Logique pour les objectifs réussis en cours
                                    if ($goal->percentage >= 100 && $goal->status_color === 'bg-primary') {
                                         $progressBarColor = 'bg-success';
                                    }
                                    
                                    // Logique spécifique pour les charges : dépasser la cible est NEGATIF (Rouge)
                                    if ($goal->type === 'charges') {
                                        if ($goal->percentage > 100) {
                                            $progressBarColor = 'bg-danger'; // Mauvaise nouvelle: charges dépassées
                                        } else {
                                            $progressBarColor = 'bg-success'; // Bonne nouvelle: charges maîtrisées
                                        }
                                    }
                                @endphp
                                
                                <div class="progress" style="height: 18px; min-width: 100px;">
                                    <div 
                                        class="progress-bar {{ $progressBarColor }} text-white fw-bold" 
                                        role="progressbar" 
                                        style="width: {{ $percentage }}%;" 
                                        aria-valuenow="{{ $percentage }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        {{ number_format($goal->percentage, 1) }}%
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Statut (utilise $goal->status_label et $goal->status_color du contrôleur) --}}
                            <td><span class="badge {{ $goal->status_color }}">{{ $goal->status_label }}</span></td>

                            {{-- ACTIONS --}}
                            <td class="action-btns">
                                {{-- BOUTON D'ÉDITION AJAX : La classe 'btn-edit-goal' est écoutée par le JS --}}
                                <a href="#" class="btn btn-sm btn-info btn-edit-goal" data-goal-token="{{ $goal->token }}">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- BOUTON DE SUPPRESSION --}}
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteGoalModal-{{ $goal->token }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- LIENS DE PAGINATION (Vue customisée) --}}
        <div class="card-footer bg-body-tertiary">
            <div class="custom-pagination-container">
                 {{-- Vérifiez bien que 'custom' est le nom de votre vue de pagination --}}
                 {{ $goals->links('vendor.pagination.custom') }} 
            </div>
        </div>

        {{-- INCLUSION DES MODALES DE SUPPRESSION DYNAMIQUES UNIQUEMENT --}}
        @foreach ($goals as $goal)
        <div class="modal fade" id="deleteGoalModal-{{ $goal->token }}" tabindex="-1" aria-labelledby="deleteGoalModalLabel-{{ $goal->token }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteGoalModalLabel-{{ $goal->token }}">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Êtes-vous sûr de vouloir supprimer l'objectif de **{{ $goal->type_label }}** ({{ ucfirst($goal->periodicity) }}) ? Cette action est irréversible.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        {{-- La classe btn-confirm-delete est écoutée par le JS de index.blade.php --}}
                        <button type="button" class="btn btn-danger btn-confirm-delete" data-goal-token="{{ $goal->token }}">Oui, Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    @endif
</div>