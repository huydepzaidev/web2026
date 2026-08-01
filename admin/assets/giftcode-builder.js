(() => {
    const config = window.giftCodeBuilderConfig;
    const root = document.querySelector('[data-gift-builder]');
    if (!config || !root) return;

    const rewards = Array.isArray(config.initialRewards) ? config.initialRewards : [];
    const optionMap = new Map(config.options.map((option) => [Number(option.id), option]));
    const rewardsInput = document.querySelector('#rewards_json');
    const rewardList = root.querySelector('[data-reward-list]');
    const emptyState = root.querySelector('[data-reward-empty]');
    const modal = document.querySelector('#itemCatalogModal');
    const modalBody = modal.querySelector('[data-catalog-results]');
    const searchInput = modal.querySelector('[data-catalog-search]');
    const pageLabel = modal.querySelector('[data-catalog-page]');
    const previousButton = modal.querySelector('[data-catalog-prev]');
    const nextButton = modal.querySelector('[data-catalog-next]');
    let catalogPage = 1;
    let catalogPages = 1;
    let searchTimer = null;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;').replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const optionPreview = (name, param) => {
        if (!name) return '';
        return name.includes('#') ? name.replaceAll('#', String(param)) : `${name} (${param})`;
    };

    const sync = () => {
        rewardsInput.value = JSON.stringify(rewards.map((reward) => ({
            id: Number(reward.id),
            quantity: Number(reward.quantity) || 1,
            options: reward.id < 0 ? [] : (reward.options || []).map((option) => ({
                id: Number(option.id),
                param: Number(option.param) || 0,
            })),
        })));
    };

    const normalizeSearch = (value) => String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const findOptions = (query, reward) => {
        const normalized = normalizeSearch(query);
        const usedIds = new Set(reward.options.map((option) => Number(option.id)));
        if (!normalized) return [];
        return config.options.filter((option) => {
            if (usedIds.has(Number(option.id))) return false;
            return String(option.id) === normalized
                || String(option.id).startsWith(normalized)
                || normalizeSearch(option.name).includes(normalized);
        }).slice(0, 10);
    };

    const render = () => {
        rewardList.innerHTML = '';
        emptyState.hidden = rewards.length > 0;
        rewards.forEach((reward, rewardIndex) => {
            reward.options = Array.isArray(reward.options) ? reward.options : [];
            const card = document.createElement('article');
            card.className = 'reward-card';
            const isSpecial = Number(reward.id) < 0;
            card.innerHTML = `
                <header class="reward-card-head">
                    <span class="reward-order">${rewardIndex + 1}</span>
                    <div><strong>${escapeHtml(reward.name || `Item #${reward.id}`)}</strong>
                    <small>ID ${reward.id}${reward.type !== undefined ? ` · Type ${reward.type}` : ''}</small></div>
                    <button type="button" class="btn btn-danger btn-sm" data-remove-reward>Xóa</button>
                </header>
                <div class="reward-main">
                    <div class="form-group">
                        <label>${isSpecial ? 'SỐ LƯỢNG CỘNG' : 'SỐ LƯỢNG VẬT PHẨM'}</label>
                        <input class="form-control" type="number" min="1" max="2000000000" value="${Number(reward.quantity) || 1}" data-reward-quantity>
                    </div>
                    <div class="reward-summary">${isSpecial ? 'Phần thưởng tiền tệ không cần option.' : `${reward.options.length} option đã chọn`}</div>
                </div>
                ${isSpecial ? '' : `
                <div class="option-section">
                    <div class="option-title"><div><strong>Option của vật phẩm</strong><small>Tìm bằng tên hoặc ID, sau đó bấm kết quả để thêm.</small></div><span class="option-count">${reward.options.length} option</span></div>
                    <div class="option-search-box">
                        <div class="option-search-control">
                            <span aria-hidden="true">⌕</span>
                            <input type="search" autocomplete="off" data-option-search placeholder="Ví dụ: chí mạng, HP, không thể giao dịch hoặc 14...">
                            <kbd>Enter</kbd>
                        </div>
                        <div class="option-suggestions" data-option-suggestions hidden></div>
                    </div>
                    <div data-option-list></div>
                </div>`}
            `;

            card.querySelector('[data-remove-reward]').addEventListener('click', () => {
                rewards.splice(rewardIndex, 1);
                render();
            });
            card.querySelector('[data-reward-quantity]').addEventListener('input', (event) => {
                reward.quantity = Math.max(1, Number(event.target.value) || 1);
                sync();
            });

            if (!isSpecial) {
                const list = card.querySelector('[data-option-list]');
                reward.options.forEach((itemOption, optionIndex) => {
                    const option = optionMap.get(Number(itemOption.id));
                    const row = document.createElement('div');
                    row.className = 'option-row';
                    row.innerHTML = `
                        <div class="option-identity">
                            <span>#${Number(itemOption.id)}</span>
                            <div><strong>${escapeHtml(option?.name || 'Option không tồn tại')}</strong><small>ID option ${Number(itemOption.id)}</small></div>
                        </div>
                        <div class="form-group option-param">
                            <label>PARAM (# / %)</label>
                            <input class="form-control" type="number" min="-2147483648" max="2147483647" value="${Number(itemOption.param) || 0}" data-option-param>
                        </div>
                        <div class="option-live" data-option-preview>${escapeHtml(optionPreview(option?.name, itemOption.param))}</div>
                        <button class="option-remove" type="button" data-remove-option aria-label="Xóa option">×</button>
                    `;
                    const param = row.querySelector('[data-option-param]');
                    const preview = row.querySelector('[data-option-preview]');
                    const updatePreview = () => {
                        itemOption.param = Number(param.value) || 0;
                        preview.textContent = optionPreview(optionMap.get(itemOption.id)?.name, itemOption.param);
                        sync();
                    };
                    param.addEventListener('input', updatePreview);
                    row.querySelector('[data-remove-option]').addEventListener('click', () => {
                        reward.options.splice(optionIndex, 1);
                        render();
                    });
                    list.appendChild(row);
                });

                const optionSearch = card.querySelector('[data-option-search]');
                const suggestions = card.querySelector('[data-option-suggestions]');
                let matches = [];
                let activeIndex = -1;

                const closeSuggestions = () => {
                    suggestions.hidden = true;
                    suggestions.innerHTML = '';
                    activeIndex = -1;
                };
                const addOption = (option) => {
                    if (!option || reward.options.some((item) => Number(item.id) === Number(option.id))) return;
                    reward.options.push({id: Number(option.id), param: 0});
                    render();
                    const refreshedCards = rewardList.querySelectorAll('.reward-card');
                    const refreshedSearch = refreshedCards[rewardIndex]?.querySelector('[data-option-search]');
                    refreshedSearch?.focus();
                };
                const paintSuggestions = () => {
                    matches = findOptions(optionSearch.value, reward);
                    activeIndex = matches.length ? 0 : -1;
                    suggestions.innerHTML = '';
                    if (!optionSearch.value.trim()) {
                        closeSuggestions();
                        return;
                    }
                    if (!matches.length) {
                        suggestions.innerHTML = '<div class="option-no-result">Không tìm thấy option phù hợp hoặc option đã được thêm.</div>';
                        suggestions.hidden = false;
                        return;
                    }
                    matches.forEach((match, index) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `option-suggestion${index === activeIndex ? ' active' : ''}`;
                        button.innerHTML = `<span>#${match.id}</span><strong>${escapeHtml(match.name || '(Không có mô tả)')}</strong><b>Thêm +</b>`;
                        button.addEventListener('mousedown', (event) => event.preventDefault());
                        button.addEventListener('click', () => addOption(match));
                        suggestions.appendChild(button);
                    });
                    suggestions.hidden = false;
                };
                const updateActiveSuggestion = () => {
                    suggestions.querySelectorAll('.option-suggestion').forEach((element, index) => {
                        element.classList.toggle('active', index === activeIndex);
                    });
                    suggestions.querySelector('.option-suggestion.active')?.scrollIntoView({block: 'nearest'});
                };

                optionSearch.addEventListener('input', paintSuggestions);
                optionSearch.addEventListener('focus', paintSuggestions);
                optionSearch.addEventListener('blur', () => setTimeout(closeSuggestions, 120));
                optionSearch.addEventListener('keydown', (event) => {
                    if (event.key === 'ArrowDown' && matches.length) {
                        event.preventDefault();
                        activeIndex = (activeIndex + 1) % matches.length;
                        updateActiveSuggestion();
                    } else if (event.key === 'ArrowUp' && matches.length) {
                        event.preventDefault();
                        activeIndex = (activeIndex - 1 + matches.length) % matches.length;
                        updateActiveSuggestion();
                    } else if (event.key === 'Enter') {
                        event.preventDefault();
                        if (matches.length && activeIndex >= 0) {
                            addOption(matches[activeIndex]);
                        }
                    } else if (event.key === 'Escape') {
                        closeSuggestions();
                    }
                });
            }
            rewardList.appendChild(card);
        });
        sync();
    };

    const openModal = () => {
        modal.hidden = false;
        document.body.classList.add('modal-open');
        searchInput.focus();
        loadCatalog(1);
    };
    const closeModal = () => {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
    };

    const loadCatalog = async (page = 1) => {
        modalBody.innerHTML = '<div class="catalog-loading">Đang tải dữ liệu từ database...</div>';
        const url = new URL(config.catalogUrl, window.location.href);
        url.searchParams.set('type', 'items');
        url.searchParams.set('q', searchInput.value.trim());
        url.searchParams.set('page', String(page));
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (!response.ok || data.status !== 'success') throw new Error();
            catalogPage = data.page;
            catalogPages = data.pages;
            pageLabel.textContent = `Trang ${catalogPage}/${catalogPages} · ${data.total} kết quả`;
            previousButton.disabled = catalogPage <= 1;
            nextButton.disabled = catalogPage >= catalogPages;
            modalBody.innerHTML = '';
            data.items.forEach((item) => {
                const selected = rewards.some((reward) => Number(reward.id) === Number(item.id));
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'catalog-item';
                row.disabled = selected;
                row.innerHTML = `
                    <span class="catalog-id">${item.id}</span>
                    <span><strong>${escapeHtml(item.name)}</strong><small>${escapeHtml(item.description || 'Không có mô tả')} · Type ${item.type} · Phái ${item.gender}</small></span>
                    <b>${selected ? 'Đã chọn' : 'Chọn +'}</b>`;
                row.addEventListener('click', () => {
                    rewards.push({...item, quantity: 1, options: []});
                    render();
                    closeModal();
                });
                modalBody.appendChild(row);
            });
            if (!data.items.length) modalBody.innerHTML = '<div class="catalog-loading">Không tìm thấy vật phẩm.</div>';
        } catch {
            modalBody.innerHTML = '<div class="catalog-loading error">Không thể tải danh mục. Vui lòng thử lại.</div>';
        }
    };

    root.querySelector('[data-open-catalog]').addEventListener('click', openModal);
    modal.querySelectorAll('[data-close-catalog]').forEach((button) => button.addEventListener('click', closeModal));
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadCatalog(1), 250);
    });
    previousButton.addEventListener('click', () => loadCatalog(catalogPage - 1));
    nextButton.addEventListener('click', () => loadCatalog(catalogPage + 1));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });
    root.closest('form').addEventListener('submit', (event) => {
        sync();
        if (!rewards.length) {
            event.preventDefault();
            window.alert('Giftcode phải có ít nhất một phần thưởng.');
        }
    });

    render();
})();
