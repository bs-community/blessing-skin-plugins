import { render, fireEvent, waitFor } from '@testing-library/svelte'
import { tick } from 'svelte'
import Description from './Description.svelte'

const { fetch, t } = globalThis.blessing

test('render description', async () => {
  const spy = jest.spyOn(fetch, 'get').mockResolvedValue('<div id="md"></div>')
  render(Description, { props: { tid: 1 } })
  await waitFor(() => expect(spy).toBeCalledWith('/texture/1/description'))
  await tick()
  await tick()
  expect(document.querySelector('#md')).toBeInTheDocument()
})

test('hide description for non-uploader', async () => {
  const spy = jest.spyOn(fetch, 'get').mockResolvedValue('')
  const { queryByText } = render(Description, {
    props: { tid: 1, canEdit: false },
  })
  await waitFor(() => expect(spy).toBeCalledWith('/texture/1/description'))
  await tick()
  await tick()
  expect(queryByText(t('texture-description.empty'))).not.toBeInTheDocument()
})

test('edit is not allowed', async () => {
  const spy = jest.spyOn(fetch, 'get').mockResolvedValue('<div id="md"></div>')
  const { queryByTitle } = render(Description, { props: { tid: 1 } })
  await waitFor(() => expect(spy).toBeCalledWith('/texture/1/description'))
  expect(queryByTitle(t('texture-description.edit'))).not.toBeInTheDocument()
})

describe('edit description', () => {
  it('cancelled', async () => {
    const spyGet = jest
      .spyOn(fetch, 'get')
      .mockResolvedValueOnce('<div id="md">a</div>')
      .mockResolvedValueOnce('a')
    const spyPut = jest.spyOn(fetch, 'put')
    const { getByTitle, getByText } = render(Description, {
      props: { tid: 1, canEdit: true },
    })
    await waitFor(() => expect(spyGet).toBeCalledWith('/texture/1/description'))

    fireEvent.click(getByTitle(t('texture-description.edit')))
    await waitFor(() =>
      expect(spyGet).toBeCalledWith('/texture/1/description', { raw: true }),
    )
    await tick()
    await tick()

    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(spyPut).not.toBeCalled())
  })

  it('max length exceeded', async () => {
    const spy = jest
      .spyOn(fetch, 'get')
      .mockResolvedValueOnce('<div id="md">a</div>')
      .mockResolvedValueOnce('a')
    const { getByTitle, getByText, getByDisplayValue, queryByText } = render(
      Description,
      {
        props: { tid: 1, canEdit: true, maxLength: 2 },
      },
    )
    await waitFor(() => expect(spy).toBeCalledWith('/texture/1/description'))

    fireEvent.click(getByTitle(t('texture-description.edit')))
    await waitFor(() =>
      expect(spy).toBeCalledWith('/texture/1/description', { raw: true }),
    )
    await tick()
    await tick()

    fireEvent.input(getByDisplayValue('a'), { target: { value: 'abcd' } })
    await tick()
    expect(
      queryByText(t('texture-description.exceeded', { max: 2 })),
    ).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
  })

  it('submit description', async () => {
    const spyGet = jest
      .spyOn(fetch, 'get')
      .mockResolvedValueOnce('<div id="md">a</div>')
      .mockResolvedValueOnce('a')
    const spyPut = jest.spyOn(fetch, 'put').mockResolvedValue('<p>abcd</p>')
    const { getByTitle, getByText, getByDisplayValue, queryByText } = render(
      Description,
      {
        props: { tid: 1, canEdit: true },
      },
    )
    await waitFor(() => expect(spyGet).toBeCalledWith('/texture/1/description'))

    fireEvent.click(getByTitle(t('texture-description.edit')))
    await waitFor(() =>
      expect(spyGet).toBeCalledWith('/texture/1/description', { raw: true }),
    )
    await tick()
    await tick()

    fireEvent.input(getByDisplayValue('a'), { target: { value: 'abcd' } })
    fireEvent.click(getByText(t('general.submit')))
    await waitFor(() =>
      expect(spyPut).toBeCalledWith('/texture/1/description', {
        description: 'abcd',
      }),
    )
    await tick()
    await tick()
    expect(queryByText('abcd')).toBeInTheDocument()
  })
})
