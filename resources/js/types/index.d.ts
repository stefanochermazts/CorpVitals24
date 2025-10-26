export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  roles: string[];
  permissions: string[];
  team_id: number;
  company_id: number;
  created_at: string;
  updated_at: string;
}

export interface Team {
  id: number;
  name: string;
  slug: string;
  created_at: string;
  updated_at: string;
}

export interface Company {
  id: number;
  team_id: number;
  name: string;
  vat_number?: string;
  fiscal_code?: string;
  created_at: string;
  updated_at: string;
}

export interface FlashMessages {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

export interface PageProps extends Record<string, any> {
  auth: {
    user: User | null;
  };
  flash: FlashMessages;
  ziggy?: any;
}

export interface PaginatedData<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

