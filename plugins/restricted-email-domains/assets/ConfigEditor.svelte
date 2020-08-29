<script lang="ts">
  import { onMount } from 'svelte'
  import { nanoid } from 'nanoid'
  import { fetch, t, notify } from 'blessing-skin'
  import DomainsList from './DomainsList.svelte'

  type Item = { id: string; value: string }

  let isLoading = true
  let allowList: Item[] = []
  let isAllowListDirty = false
  let denyList: Item[] = []
  let isDenyListDirty = false

  onMount(async () => {
    const {
      allow,
      deny,
    }: { allow: string[]; deny: string[] } = await fetch.get(
      '/admin/restricted-email-domains',
    )
    allowList = allow.map((value) => ({ id: nanoid(), value }))
    denyList = deny.map((value) => ({ id: nanoid(), value }))
    isLoading = false
  })

  function createAllowItem() {
    isAllowListDirty = true
    const item: Item = { id: nanoid(), value: '' }
    allowList = [...allowList, item]
  }

  function removeAllowItem(id: string) {
    isAllowListDirty = true
    allowList = allowList.filter((item) => item.id !== id)
  }

  async function saveAllowList() {
    const resp = await fetch.put(
      '/admin/restricted-email-domains/allow',
      allowList.map((item) => item.value),
    )
    if (resp === '') {
      notify.toast.success(t('restricted-email-domains.ok'))
      isAllowListDirty = false
    }
  }

  function createDenyItem() {
    isDenyListDirty = true
    const item: Item = { id: nanoid(), value: '' }
    denyList = [...denyList, item]
  }

  function removeDenyItem(id: string) {
    isDenyListDirty = true
    denyList = denyList.filter((item) => item.id !== id)
  }

  async function saveDenyList() {
    const resp = await fetch.put(
      '/admin/restricted-email-domains/deny',
      denyList.map((item) => item.value),
    )
    if (resp === '') {
      notify.toast.success(t('restricted-email-domains.ok'))
      isDenyListDirty = false
    }
  }
</script>

<div class="row">
  <div class="col-lg-6">
    <DomainsList
      list={allowList}
      title={t('restricted-email-domains.allow.title')}
      cardType="success"
      isDirty={isAllowListDirty}
      {isLoading}
      on:edit={() => (isAllowListDirty = true)}
      on:add={createAllowItem}
      on:remove={(event) => removeAllowItem(event.detail)}
      on:save={saveAllowList} />
  </div>
  <div class="col-lg-6">
    <DomainsList
      list={denyList}
      title={t('restricted-email-domains.deny.title')}
      cardType="danger"
      isDirty={isDenyListDirty}
      {isLoading}
      on:edit={() => (isDenyListDirty = true)}
      on:add={createDenyItem}
      on:remove={(event) => removeDenyItem(event.detail)}
      on:save={saveDenyList} />
  </div>
</div>
