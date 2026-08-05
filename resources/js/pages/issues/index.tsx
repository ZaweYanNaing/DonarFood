import { Head, Link } from '@inertiajs/react';
import { Plus, MapPin, Users } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AppLayout } from '@/components/app-shell';

interface Issue {
    id: number;
    title: string;
    description: string;
    location: string | null;
    status: string;
    required_items: string | null;
    created_at: string;
    expires_at: string | null;
    user: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
    donor_count: number;
    has_donations: boolean;
}

interface Pagination {
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    issues: Issue[];
    pagination: Pagination;
    can: {
        create: boolean;
    };
}

export default function IssuesIndex({ issues, pagination, can }: Props) {
    const statusColors: Record<string, string> = {
        open: 'bg-green-100 text-green-800',
        in_progress: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-blue-100 text-blue-800',
        closed: 'bg-gray-100 text-gray-800',
    };

    return (
        <AppLayout>
            <Head title="Donation Issues" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Donation Issues</h1>
                        <p className="text-muted-foreground mt-1">
                            Browse and participate in food donation opportunities
                        </p>
                    </div>
                    {can.create && (
                        <Link href="/issues/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Create Issue
                            </Button>
                        </Link>
                    )}
                </div>

                {issues.length === 0 ? (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-muted-foreground">
                                No donation issues yet. Be the first to create one!
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {issues.map((issue) => (
                            <Link key={issue.id} href={`/issues/${issue.id}`}>
                                <Card className="h-full hover:shadow-md transition-shadow cursor-pointer">
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <Badge className={statusColors[issue.status] || 'bg-gray-100'}>
                                                {issue.status.replace('_', ' ')}
                                            </Badge>
                                            <span className="text-xs text-muted-foreground">
                                                {new Date(issue.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        <CardTitle className="text-lg mt-2 line-clamp-2">
                                            {issue.title}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm text-muted-foreground line-clamp-3 mb-4">
                                            {issue.description}
                                        </p>
                                        <div className="space-y-2 text-sm">
                                            {issue.location && (
                                                <div className="flex items-center text-muted-foreground">
                                                    <MapPin className="mr-2 h-4 w-4" />
                                                    {issue.location}
                                                </div>
                                            )}
                                            <div className="flex items-center text-muted-foreground">
                                                <Users className="mr-2 h-4 w-4" />
                                                {issue.donor_count} donor{issue.donor_count !== 1 ? 's' : ''}
                                            </div>
                                            {issue.required_items && (
                                                <div className="flex items-start">
                                                    <span className="text-muted-foreground text-xs">
                                                        Needed: {issue.required_items}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="mt-4 pt-4 border-t flex items-center gap-3">
                                            {issue.user.profile_photo_url ? (
                                                <img
                                                    src={issue.user.profile_photo_url}
                                                    alt={issue.user.name}
                                                    className="w-8 h-8 rounded-full"
                                                />
                                            ) : (
                                                <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                    <span className="text-sm font-medium">
                                                        {issue.user.name.charAt(0)}
                                                    </span>
                                                </div>
                                            )}
                                            <span className="text-sm">{issue.user.name}</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}

                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing {issues.length} of {pagination.total} issues
                        </p>
                        <div className="flex gap-2">
                            {pagination.current_page > 1 && (
                                <Link href={`?page=${pagination.current_page - 1}`}>
                                    <Button variant="outline" size="sm">Previous</Button>
                                </Link>
                            )}
                            {pagination.current_page < pagination.last_page && (
                                <Link href={`?page=${pagination.current_page + 1}`}>
                                    <Button variant="outline" size="sm">Next</Button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}