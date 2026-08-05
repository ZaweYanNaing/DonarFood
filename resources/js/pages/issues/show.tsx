import { Head, Link } from '@inertiajs/react';
import { MapPin, Edit, Trash2, Heart } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AppLayout } from '@/components/app-shell';
import { Textarea } from '@/components/ui/textarea';

interface Issue {
    id: number;
    title: string;
    description: string;
    location: string | null;
    address: string | null;
    status: string;
    required_items: string | null;
    created_at: string;
    expires_at: string | null;
    user: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
    donations: Donation[];
    donor_count: number;
    total_pledged: string;
    images: Image[];
}

interface Donation {
    id: number;
    amount: string;
    items_description: string | null;
    status: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
    images: Image[];
}

interface Image {
    id: number;
    url: string;
    original_name: string | null;
}

interface Props {
    issue: Issue;
    can: {
        update: boolean;
        delete: boolean;
        donate: boolean;
        message: boolean;
    };
}

export default function IssueShow({ issue, can }: Props) {
    const [showPledgeModal, setShowPledgeModal] = useState(false);

    const statusColors: Record<string, string> = {
        open: 'bg-green-100 text-green-800',
        in_progress: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-blue-100 text-blue-800',
        closed: 'bg-gray-100 text-gray-800',
    };

    return (
        <AppLayout>
            <Head title={issue.title} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div className="space-y-2">
                        <div className="flex items-center gap-3">
                            <Badge className={statusColors[issue.status]}>
                                {issue.status.replace('_', ' ')}
                            </Badge>
                            <span className="text-sm text-muted-foreground">
                                Posted {new Date(issue.created_at).toLocaleDateString()}
                            </span>
                        </div>
                        <h1 className="text-3xl font-bold">{issue.title}</h1>
                        {issue.location && (
                            <div className="flex items-center text-muted-foreground">
                                <MapPin className="mr-2 h-4 w-4" />
                                {issue.location}
                                {issue.address && ` - ${issue.address}`}
                            </div>
                        )}
                    </div>
                    <div className="flex gap-2">
                        {can.update && (
                            <Link href={`/issues/${issue.id}/edit`}>
                                <Button variant="outline">
                                    <Edit className="mr-2 h-4 w-4" />
                                    Edit
                                </Button>
                            </Link>
                        )}
                        {can.delete && (
                            <Link href={`/issues/${issue.id}`} method="delete" as="button">
                                <Button variant="destructive" onClick={(e) => {
                                    if (!confirm('Are you sure you want to delete this issue?')) {
                                        e.preventDefault();
                                    }
                                }}>
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </Link>
                        )}
                        {can.donate && (
                            <Button onClick={() => setShowPledgeModal(true)}>
                                <Heart className="mr-2 h-4 w-4" />
                                Pledge Donation
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Description */}
                        <Card>
                            <CardHeader>
                                <CardTitle>About This Donation</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap">{issue.description}</p>
                                {issue.required_items && (
                                    <div className="mt-4 p-4 bg-muted rounded-lg">
                                        <h4 className="font-medium mb-2">Items Needed:</h4>
                                        <p>{issue.required_items}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Donations List */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Donations ({issue.donor_count})</span>
                                    {issue.total_pledged && (
                                        <span className="text-sm font-normal text-muted-foreground">
                                            Total: {issue.total_pledged}
                                        </span>
                                    )}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {issue.donations.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-8">
                                        No donations yet. Be the first to contribute!
                                    </p>
                                ) : (
                                    <div className="space-y-4">
                                        {issue.donations.map((donation) => (
                                            <div key={donation.id} className="flex gap-4 p-4 border rounded-lg">
                                                {donation.user.profile_photo_url ? (
                                                    <img
                                                        src={donation.user.profile_photo_url}
                                                        alt={donation.user.name}
                                                        className="w-10 h-10 rounded-full"
                                                    />
                                                ) : (
                                                    <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                        <span className="font-medium">
                                                            {donation.user.name.charAt(0)}
                                                        </span>
                                                    </div>
                                                )}
                                                <div className="flex-1">
                                                    <div className="flex items-center justify-between">
                                                        <Link href={`/profile/${donation.user.id}`} className="font-medium hover:underline">
                                                            {donation.user.name}
                                                        </Link>
                                                        <span className="text-sm text-muted-foreground">
                                                            {new Date(donation.created_at).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                    <p className="font-semibold text-green-600 mt-1">
                                                        {donation.amount}
                                                    </p>
                                                    {donation.items_description && (
                                                        <p className="text-sm text-muted-foreground mt-1">
                                                            {donation.items_description}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Organizer */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Organized By</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center gap-3">
                                    {issue.user.profile_photo_url ? (
                                        <img
                                            src={issue.user.profile_photo_url}
                                            alt={issue.user.name}
                                            className="w-12 h-12 rounded-full"
                                        />
                                    ) : (
                                        <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span className="text-xl font-medium">
                                                {issue.user.name.charAt(0)}
                                            </span>
                                        </div>
                                    )}
                                    <div>
                                        <Link href={`/profile/${issue.user.id}`} className="font-medium hover:underline">
                                            {issue.user.name}
                                        </Link>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Stats */}
                        <Card>
                            <CardContent className="pt-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">Donors</span>
                                        <span className="font-semibold">{issue.donor_count}</span>
                                    </div>
                                    {issue.expires_at && (
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Expires</span>
                                            <span className="font-semibold">
                                                {new Date(issue.expires_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Actions */}
                        <Card>
                            <CardContent className="pt-6 space-y-3">
                                <Button variant="outline" className="w-full" asChild>
                                    <Link href={`/profile/${issue.user.id}`}>
                                        View Organizer's Profile
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            {/* Pledge Modal */}
            <Dialog open={showPledgeModal} onOpenChange={setShowPledgeModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Pledge a Donation</DialogTitle>
                    </DialogHeader>
                    <form action={`/issues/${issue.id}/donations`} method="POST" encType="multipart/form-data" className="space-y-4">
                        <input type="hidden" name="_token" value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content} />
                        <div>
                            <label className="text-sm font-medium">What are you donating?</label>
                            <input
                                type="text"
                                name="amount"
                                required
                                placeholder="e.g., 5 kg rice, 20 bread loaves"
                                className="mt-1 w-full rounded-md border px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="text-sm font-medium">Description (optional)</label>
                            <Textarea
                                name="items_description"
                                placeholder="Additional details about your donation..."
                                rows={3}
                            />
                        </div>
                        <div>
                            <label className="text-sm font-medium">Notes (optional)</label>
                            <Textarea
                                name="notes"
                                placeholder="Special instructions or notes..."
                                rows={2}
                            />
                        </div>
                        <div>
                            <label className="text-sm font-medium">Photos (optional)</label>
                            <input
                                type="file"
                                name="images[]"
                                accept="image/*"
                                multiple
                                className="mt-1 w-full"
                            />
                        </div>
                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="outline" onClick={() => setShowPledgeModal(false)}>
                                Cancel
                            </Button>
                            <Button type="submit">Submit Pledge</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}