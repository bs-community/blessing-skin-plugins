<script lang="ts">
  import { onDestroy, onMount } from 'svelte'

  let code = ''

  let off: () => void
  onMount(() => {
    off = globalThis.blessing.event.on(
      'beforeFetch',
      (request: { data: Record<string, string> }) => {
        request.data.invitationCode = code
      },
    )
  })
  onDestroy(() => off())
</script>

<input
  type="text"
  class="form-control"
  placeholder={globalThis.blessing.t('invitation-codes.placeholder')}
  required
  bind:value={code}
/>
<div class="input-group-append">
  <div class="input-group-text">
    <i class="fas fa-receipt" />
  </div>
</div>
