<form action="{{ route('advisor.inspections.claim', $inspection->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-success btn-sm">Claim Job</button>
</form>
